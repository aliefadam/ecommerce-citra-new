<?php

namespace App\Services;

use App\Models\CheckoutAttempt;
use App\Models\Transaction;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutIdempotencyService
{
    public function replay(int $userId, string $key, array $payload): ?JsonResponse
    {
        $attempt = CheckoutAttempt::query()
            ->where('user_id', $userId)
            ->where('idempotency_key', $key)
            ->first();
        if (! $attempt) {
            return null;
        }

        $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
        if (! hash_equals($attempt->request_hash, $hash)) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Idempotency key sudah digunakan untuk payload checkout yang berbeda.',
            ]);
        }

        if ($attempt->status === 'completed') {
            return response()->json($attempt->response_body, (int) $attempt->response_status);
        }

        if ($attempt->updated_at?->lte(now()->subMinutes(5))) {
            $transaction = Transaction::query()
                ->where('user_id', $userId)
                ->where('idempotency_key', $key)
                ->first();
            if ($transaction) {
                $body = [
                    'ok' => true,
                    'order_id' => (string) $transaction->order_id,
                    'transaction_db_id' => (int) $transaction->id,
                    'redirect_url' => route('frontend.checkout.waiting', ['orderId' => $transaction->order_id]),
                ];
                $attempt->update(['status' => 'completed', 'response_status' => 200, 'response_body' => $body]);

                return response()->json($body);
            }

            $attempt->delete();

            return null;
        }

        return response()->json([
            'code' => 'checkout_in_progress',
            'message' => 'Checkout dengan idempotency key ini sedang diproses.',
        ], 409);
    }

    public function execute(int $userId, string $key, array $payload, Closure $callback): JsonResponse
    {
        $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
        $inserted = CheckoutAttempt::query()->insertOrIgnore([
            'user_id' => $userId,
            'idempotency_key' => $key,
            'request_hash' => $hash,
            'status' => 'processing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted === 0) {
            return $this->replay($userId, $key, $payload)
                ?? response()->json(['code' => 'checkout_conflict', 'message' => 'Checkout tidak dapat diproses.'], 409);
        }

        try {
            /** @var JsonResponse $response */
            $response = $callback();
            $body = json_decode((string) $response->getContent(), true) ?? [];

            if ($response->isSuccessful()) {
                CheckoutAttempt::query()
                    ->where('user_id', $userId)
                    ->where('idempotency_key', $key)
                    ->update([
                        'status' => 'completed',
                        'response_status' => $response->getStatusCode(),
                        'response_body' => $body,
                        'updated_at' => now(),
                    ]);
            } else {
                $this->forget($userId, $key);
            }

            return $response;
        } catch (\Throwable $exception) {
            $this->forget($userId, $key);
            throw $exception;
        }
    }

    private function forget(int $userId, string $key): void
    {
        DB::table('checkout_attempts')
            ->where('user_id', $userId)
            ->where('idempotency_key', $key)
            ->delete();
    }
}
