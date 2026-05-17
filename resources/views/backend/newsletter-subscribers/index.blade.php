@extends('layouts.app')

@section('title', 'Newsletter Subscribers')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Newsletter Subscribers</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola subscriber, preview email, test send, jadwal kirim, dan histori campaign.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:bg-blue-500/10 dark:text-blue-200">
                    Total subscriber aktif: <span class="font-semibold">{{ $subscribers->where('is_subscribed', true)->count() }}</span>
                </div>
                <a href="{{ route('newsletter-subscribers.export') }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-600 transition-colors">
                    Export CSV
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Composer Newsletter</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buat newsletter, preview, kirim test, kirim langsung, atau jadwalkan.</p>
                </div>

                <form id="newsletterForm" action="{{ route('newsletter-subscribers.send') }}" method="POST" enctype="multipart/form-data" class="space-y-4" target="_self">
                    @csrf
                    <div>
                        <label for="newsletter-subject" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Subject</label>
                        <input id="newsletter-subject" type="text" name="subject" value="{{ old('subject') }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100"
                            placeholder="Contoh: Promo akhir pekan spesial" required>
                        @error('subject')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="newsletter-message" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Isi Pesan</label>
                        <textarea id="newsletter-message" name="message" rows="8"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100"
                            placeholder="Tulis isi newsletter di sini..." required>{{ old('message') }}</textarea>
                        @error('message')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div>
                            <label for="newsletter-cta-label" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Label Tombol CTA</label>
                            <input id="newsletter-cta-label" type="text" name="cta_label" value="{{ old('cta_label') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100"
                                placeholder="Contoh: Belanja Sekarang">
                            @error('cta_label')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="newsletter-cta-url" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">URL Tombol CTA</label>
                            <input id="newsletter-cta-url" type="url" name="cta_url" value="{{ old('cta_url') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100"
                                placeholder="https://example.com/promo">
                            @error('cta_url')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div>
                            <label for="newsletter-hero-image-url" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">URL Banner / Hero Image</label>
                            <input id="newsletter-hero-image-url" type="url" name="hero_image_url" value="{{ old('hero_image_url') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100"
                                placeholder="https://example.com/banner-newsletter.jpg">
                            @error('hero_image_url')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Atau Upload Banner</label>
                            <input type="file" name="hero_image_file" accept="image/*"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                            @error('hero_image_file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div>
                            <label for="newsletter-test-email" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Test Email</label>
                            <input id="newsletter-test-email" type="email" name="test_email" value="{{ old('test_email') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100"
                                placeholder="email@example.com">
                            @error('test_email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="newsletter-scheduled-at" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Jadwalkan Kirim</label>
                            <input id="newsletter-scheduled-at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-100">
                            @error('scheduled_at')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between pt-2">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Email massal akan dikirim ke <span class="font-semibold">{{ $subscribers->where('is_subscribed', true)->count() }}</span> subscriber aktif.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" formaction="{{ route('newsletter-subscribers.preview') }}" formtarget="_blank"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                                Preview
                            </button>
                            <button type="submit" formaction="{{ route('newsletter-subscribers.send-test') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-600 transition-colors">
                                Send Test
                            </button>
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                                Kirim / Jadwalkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white mb-2">Prioritas 1</h3>
                    <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                        <li>• Send test dari admin panel</li>
                        <li>• Preview newsletter</li>
                        <li>• Link unsubscribe</li>
                        <li>• Histori campaign</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white mb-2">Prioritas 2</h3>
                    <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                        <li>• Promo page umum</li>
                        <li>• Schedule send</li>
                        <li>• Upload banner langsung</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Histori Campaign</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-slate-500">Subject</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-500">Status</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-500">Recipients</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-500">Schedule / Sent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse ($campaigns as $campaign)
                                <tr>
                                    <td class="px-4 py-3.5">
                                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ $campaign->subject }}</div>
                                        <div class="text-xs text-slate-400">By {{ $campaign->creator?->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $campaign->status === 'sent' ? 'bg-emerald-50 text-emerald-700' : ($campaign->status === 'scheduled' ? 'bg-amber-50 text-amber-700' : ($campaign->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-700')) }}">{{ ucfirst($campaign->status) }}</span>
                                        @if ($campaign->test_email)
                                            <div class="text-xs text-slate-400 mt-1">Test: {{ $campaign->test_email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500">{{ number_format($campaign->recipient_count) }}</td>
                                    <td class="px-4 py-3.5 text-slate-500">
                                        <div>{{ optional($campaign->scheduled_at)->format('d M Y H:i') ?: '-' }}</div>
                                        <div class="text-xs text-slate-400">{{ optional($campaign->sent_at)->format('d M Y H:i') ?: '-' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada histori campaign.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="flex flex-col sm:flex-row gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Daftar Subscriber</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Subscriber aktif dan nonaktif newsletter.</p>
                    </div>
                    <div class="relative flex-1 sm:max-w-sm sm:ml-auto">
                        <input id="subscriberSearch" type="text" placeholder="Cari email subscriber..."
                            class="pl-4 pr-4 py-2 text-sm w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400" />
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-slate-500 w-12">#</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-500">Email</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-500">Status</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-500">Tanggal</th>
                                <th class="text-right px-4 py-3 font-semibold text-slate-500 w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="subscribersTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60"></tbody>
                    </table>
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                    <p id="subscribersPaginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                    <div class="flex items-center gap-1" id="subscribersPaginationButtons"></div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    @php
        $subscriberItems = $subscribers
            ->map(function ($subscriber) {
                return [
                    'id' => (int) $subscriber->id,
                    'email' => (string) $subscriber->email,
                    'status' => $subscriber->is_subscribed ? 'Subscribed' : 'Unsubscribed',
                    'status_class' => $subscriber->is_subscribed ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700',
                    'subscribed_at' => optional($subscriber->subscribed_at)->format('d M Y H:i') ?: '-',
                    'unsubscribe_url' => route('frontend.newsletter.unsubscribe', $subscriber->unsubscribe_token),
                    'delete_url' => route('newsletter-subscribers.destroy', $subscriber),
                ];
            })
            ->values()
            ->all();
    @endphp

    <script>
        const subscriberItems = @json($subscriberItems);

        function renderSubscriberRow(item, visibleIndex) {
            return `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${visibleIndex + 1}</td>
                    <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">
                        <div>${item.email || '-'}</div>
                        <div class="text-xs text-slate-400 break-all">${item.unsubscribe_url || '-'}</div>
                    </td>
                    <td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${item.status_class}">${item.status}</span></td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">${item.subscribed_at || '-'}</td>
                    <td class="px-4 py-3.5 text-right">
                        <form action="${item.delete_url}" method="POST" onsubmit="return confirm('Hapus subscriber ini?')" class="inline-flex">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100 transition-colors">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            `;
        }

        initAdminDataTable({
            data: subscriberItems,
            perPage: 10,
            itemLabel: 'subscribers',
            searchInputId: 'subscriberSearch',
            tbodyId: 'subscribersTableBody',
            paginationInfoId: 'subscribersPaginationInfo',
            paginationButtonsId: 'subscribersPaginationButtons',
            searchFields: ['email', 'status'],
            renderRow: (item, index) => renderSubscriberRow(item, index),
            emptyRowHtml: '<tr><td colspan="5" class="text-center py-12 text-slate-400 dark:text-slate-500">Belum ada subscriber newsletter</td></tr>',
        });
    </script>
@endsection
