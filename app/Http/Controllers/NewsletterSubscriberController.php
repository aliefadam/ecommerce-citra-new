<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterCampaign as NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewsletterSubscriberController extends Controller
{
    public function index(): View
    {
        $subscribers = NewsletterSubscriber::query()
            ->latest('subscribed_at')
            ->latest('id')
            ->get();

        $campaigns = NewsletterCampaign::query()
            ->with(['creator:id,name', 'sender:id,name'])
            ->latest()
            ->limit(20)
            ->get();

        return view('backend.newsletter-subscribers.index', compact('subscribers', 'campaigns'));
    }

    public function export(): Response
    {
        $subscribers = NewsletterSubscriber::query()
            ->orderBy('email')
            ->get(['email', 'is_subscribed', 'subscribed_at', 'unsubscribed_at', 'created_at']);

        $filename = 'newsletter-subscribers-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($subscribers): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Email', 'Subscribed', 'Subscribed At', 'Unsubscribed At', 'Created At']);

            foreach ($subscribers as $subscriber) {
                fputcsv($handle, [
                    (string) $subscriber->email,
                    $subscriber->is_subscribed ? 'Yes' : 'No',
                    optional($subscriber->subscribed_at)->format('Y-m-d H:i:s') ?: '',
                    optional($subscriber->unsubscribed_at)->format('Y-m-d H:i:s') ?: '',
                    optional($subscriber->created_at)->format('Y-m-d H:i:s') ?: '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function preview(Request $request): View
    {
        $payload = $this->validatedPayload($request, false);

        return view('emails.newsletter-campaign', [
            'subjectLine' => $payload['subject'],
            'messageBody' => $payload['message'],
            'ctaLabel' => $payload['cta_label'],
            'ctaUrl' => $payload['cta_url'],
            'heroImageUrl' => $payload['hero_image'],
            'storeName' => (string) ($request->attributes->get('appStoreName') ?? 'Ecommerce Citra'),
            'unsubscribeUrl' => url('/newsletter/unsubscribe/preview-only'),
        ]);
    }

    public function sendTest(Request $request, ImageOptimizer $imageOptimizer): RedirectResponse
    {
        $payload = $this->validatedPayload($request, true, $imageOptimizer);
        $testEmail = trim((string) $request->input('test_email'));

        Mail::to($testEmail)->send($this->buildMailable($payload, url('/newsletter/unsubscribe/preview-only')));

        NewsletterCampaign::query()->create([
            'subject' => $payload['subject'],
            'message' => $payload['message'],
            'cta_label' => $payload['cta_label'],
            'cta_url' => $payload['cta_url'],
            'hero_image' => $payload['hero_image'],
            'test_email' => $testEmail,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Test newsletter berhasil dikirim ke ' . $testEmail . '.');
    }

    public function send(Request $request, ImageOptimizer $imageOptimizer): RedirectResponse
    {
        $payload = $this->validatedPayload($request, true, $imageOptimizer);
        $scheduledAt = $request->filled('scheduled_at') ? now()->parse((string) $request->input('scheduled_at')) : null;

        $campaign = NewsletterCampaign::query()->create([
            'subject' => $payload['subject'],
            'message' => $payload['message'],
            'cta_label' => $payload['cta_label'],
            'cta_url' => $payload['cta_url'],
            'hero_image' => $payload['hero_image'],
            'status' => $scheduledAt && $scheduledAt->isFuture() ? 'scheduled' : 'draft',
            'scheduled_at' => $scheduledAt,
            'created_by' => auth()->id(),
        ]);

        if ($scheduledAt && $scheduledAt->isFuture()) {
            return back()->with('success', 'Newsletter dijadwalkan untuk ' . $scheduledAt->format('d M Y H:i') . '.');
        }

        $this->dispatchCampaign($campaign, auth()->id());

        return back()->with('success', 'Newsletter berhasil diproses untuk ' . $campaign->recipient_count . ' subscriber.');
    }

    public function unsubscribe(string $token): View
    {
        $subscriber = NewsletterSubscriber::query()
            ->where('unsubscribe_token', $token)
            ->firstOrFail();

        if ($subscriber->is_subscribed) {
            $subscriber->update([
                'is_subscribed' => false,
                'unsubscribed_at' => now(),
            ]);
        }

        return view('frontend.newsletter-unsubscribed', compact('subscriber'));
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->delete();

        return back()->with('success', 'Subscriber berhasil dihapus.');
    }

    public function dispatchCampaign(NewsletterCampaign $campaign, ?int $senderId = null): void
    {
        $subscribers = NewsletterSubscriber::query()
            ->where('is_subscribed', true)
            ->orderBy('email')
            ->get();

        if ($subscribers->isEmpty()) {
            $campaign->update([
                'status' => 'failed',
                'last_error' => 'Belum ada subscriber aktif.',
            ]);

            return;
        }

        $campaign->update([
            'status' => 'draft',
            'last_error' => null,
        ]);

        foreach ($subscribers->chunk(50) as $chunk) {
            $emails = $chunk->pluck('email')->filter()->values()->all();
            $primaryRecipient = array_shift($emails);
            $unsubscribeUrl = url('/newsletter/unsubscribe/' . $chunk->first()->unsubscribe_token);

            $mailer = Mail::to($primaryRecipient);
            if (!empty($emails)) {
                $mailer->bcc($emails);
            }

            $mailer->send($this->buildMailable([
                'subject' => $campaign->subject,
                'message' => $campaign->message,
                'cta_label' => $campaign->cta_label,
                'cta_url' => $campaign->cta_url,
                'hero_image' => $campaign->hero_image,
            ], $unsubscribeUrl));
        }

        $campaign->update([
            'status' => 'sent',
            'recipient_count' => $subscribers->count(),
            'sent_at' => now(),
            'sent_by' => $senderId,
        ]);
    }

    private function validatedPayload(Request $request, bool $strict = true, ?ImageOptimizer $imageOptimizer = null): array
    {
        $rules = [
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:10000'],
            'cta_label' => ['nullable', 'string', 'max:50'],
            'cta_url' => ['nullable', 'url', 'max:500'],
            'hero_image_url' => ['nullable', 'url', 'max:1000'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ];

        if ($strict) {
            $rules['test_email'] = ['nullable', 'email'];
            $rules['scheduled_at'] = ['nullable', 'date'];
        }

        $validated = $request->validate($rules, [
            'subject.required' => 'Subject newsletter wajib diisi.',
            'message.required' => 'Isi newsletter wajib diisi.',
            'cta_url.url' => 'Link tombol CTA harus berupa URL yang valid.',
            'hero_image_url.url' => 'Link gambar banner harus berupa URL yang valid.',
        ]);

        $ctaLabel = trim((string) ($validated['cta_label'] ?? '')) ?: null;
        $ctaUrl = trim((string) ($validated['cta_url'] ?? '')) ?: null;
        if (($ctaLabel && !$ctaUrl) || (!$ctaLabel && $ctaUrl)) {
            throw ValidationException::withMessages([
                'cta_url' => 'Tombol CTA harus diisi lengkap: label dan URL.',
            ]);
        }

        $heroImage = trim((string) ($validated['hero_image_url'] ?? '')) ?: null;
        if ($imageOptimizer && $request->hasFile('hero_image_file')) {
            $heroImage = $imageOptimizer->storeWebp($request->file('hero_image_file'), 'newsletter', 1600, 900, 82);
            $heroImage = asset('storage/' . ltrim($heroImage, '/'));
        }

        return [
            'subject' => trim((string) $validated['subject']),
            'message' => trim((string) $validated['message']),
            'cta_label' => $ctaLabel,
            'cta_url' => $ctaUrl,
            'hero_image' => $heroImage,
        ];
    }

    private function buildMailable(array $payload, string $unsubscribeUrl): NewsletterCampaignMail
    {
        return new NewsletterCampaignMail(
            $payload['subject'],
            $payload['message'],
            $payload['cta_label'] ?? null,
            $payload['cta_url'] ?? null,
            $payload['hero_image'] ?? null,
            $unsubscribeUrl,
        );
    }
}
