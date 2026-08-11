<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCheckoutAccess
{
    /**
     * Allow authenticated checkout flows and guest buy-now checkout sessions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        if (session('checkout.source') === 'buy_now') {
            $orderId = (string) ($request->route('orderId') ?? '');
            $ownsOrder = $orderId === ''
                || in_array($orderId, session('guest_owned_orders', []), true)
                || session()->has('checkout_waiting.'.$orderId);

            if ($ownsOrder) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Silakan login atau mulai checkout melalui Beli Sekarang.',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
