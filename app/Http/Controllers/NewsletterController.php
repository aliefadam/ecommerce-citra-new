<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function store(): RedirectResponse
    {
        $validated = request()->validate([
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $email = strtolower(trim((string) $validated['email']));

        $subscriber = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($subscriber && $subscriber->is_subscribed) {
            return back()
                ->withInput()
                ->with('newsletter_error', 'Email itu sudah terdaftar di newsletter.');
        }

        if ($subscriber && !$subscriber->is_subscribed) {
            $subscriber->update([
                'is_subscribed' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);

            return back()->with('newsletter_success', 'Email berhasil didaftarkan ulang ke newsletter.');
        }

        NewsletterSubscriber::query()->create([
            'email' => $email,
            'subscribed_at' => now(),
            'is_subscribed' => true,
        ]);

        return back()->with('newsletter_success', 'Berhasil subscribe newsletter.');
    }
}
