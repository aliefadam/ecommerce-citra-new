<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ShipmentTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function index(Request $request, ShipmentTrackingService $shipmentTrackingService): View
    {
        $orderId = strtoupper(trim((string) $request->query('order_id', '')));
        $verifiedOrders = session('verified_orders', []);
        $transaction = null;
        $shipmentTracking = null;

        if ($orderId !== '' && in_array($orderId, $verifiedOrders, true)) {
            $transaction = Transaction::query()
                ->with(['details', 'company'])
                ->where('order_id', $orderId)
                ->first();

            if ($transaction) {
                $shipmentTracking = $shipmentTrackingService->forTransaction($transaction);
            }
        }

        return view('frontend.order-tracking', compact('orderId', 'transaction', 'shipmentTracking'));
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'order_id' => ['required', 'string', 'max:100'],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $orderId = strtoupper(trim((string) $validated['order_id']));
        $rateLimitKey = $this->rateLimitKey($request, $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return $this->rateLimitedResponse($request, $rateLimitKey);
        }

        $transaction = Transaction::query()
            ->where('order_id', $orderId)
            ->where(function ($query) use ($email) {
                $query
                    ->whereRaw('LOWER(manual_customer_email) = ?', [$email])
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(email) = ?', [$email]));
            })
            ->first();

        if (! $transaction) {
            RateLimiter::hit($rateLimitKey, 15 * 60);

            if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
                return $this->rateLimitedResponse($request, $rateLimitKey);
            }

            return back()
                ->withErrors(['tracking' => 'Email atau nomor order tidak cocok. Periksa kembali data yang dimasukkan.'])
                ->withInput();
        }

        RateLimiter::clear($rateLimitKey);
        $verifiedOrders = collect(session('verified_orders', []))
            ->push($transaction->order_id)
            ->unique()
            ->values()
            ->all();
        session(['verified_orders' => $verifiedOrders]);

        return redirect()->route('frontend.order-tracking.index', [
            'order_id' => $transaction->order_id,
        ]);
    }

    private function rateLimitKey(Request $request, string $email): string
    {
        return 'order-tracking:'.hash('sha256', (string) $request->ip().'|'.$email);
    }

    private function rateLimitedResponse(Request $request, string $key): RedirectResponse
    {
        $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));

        return back()
            ->withErrors(['tracking' => "Terlalu banyak percobaan, coba lagi dalam {$minutes} menit."])
            ->withInput()
            ->setStatusCode(429);
    }
}
