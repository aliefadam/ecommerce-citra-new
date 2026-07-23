<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <script>
            (function() {
                const html = document.documentElement;
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) html.classList.add('dark');
            })();
        </script>
        <title>Open Catalog API - Dokumentasi</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet" />
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            jakarta: ['Plus Jakarta Sans', 'sans-serif']
                        }
                    }
                }
            }
        </script>
        <style>
            * {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>
    </head>

    <body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-300">
        <main class="mx-auto max-w-5xl p-4 sm:p-8">
            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Open Catalog API</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-3xl">
                    Dokumentasi <span class="font-semibold">Open Catalog API</span> — API publik <span class="font-semibold">read-only</span>
                    untuk menampilkan produk &amp; kategori tiap perusahaan di website/sistem lain. Tanpa API key: perusahaan
                    cukup ditentukan lewat <span class="font-mono text-xs">slug</span> di URL. Halaman ini bisa diakses
                    tanpa login dan aman dibagikan ke pihak eksternal.
                </p>
            </div>

            {{-- Info ringkas --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
                @foreach ([
                    ['zap', 'Metode', 'GET (read-only)'],
                    ['braces', 'Format', 'JSON'],
                    ['gauge', 'Rate limit', '120 / menit per IP'],
                    ['timer', 'Cache', '5 menit + ETag'],
                ] as $info)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
                        <div class="flex items-center gap-2 text-slate-400">
                            <i data-lucide="{{ $info[0] }}" class="w-4 h-4"></i>
                            <span class="text-xs font-semibold uppercase tracking-wide">{{ $info[1] }}</span>
                        </div>
                        <div class="mt-1.5 text-sm font-bold text-slate-800 dark:text-slate-100">{{ $info[2] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Base URL per perusahaan --}}
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-700/60">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Base URL per Perusahaan</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Salin base URL perusahaan, lalu tambahkan <span class="font-mono">/products</span> atau <span class="font-mono">/categories</span>.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Perusahaan</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Slug</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-500 dark:text-slate-400">Base URL</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse ($companies as $company)
                                @php($companyUrl = $baseUrl . '/' . $company->slug)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100">{{ $company->name }}</td>
                                    <td class="px-5 py-3.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $company->slug }}</code></td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <code class="truncate max-w-xs text-xs text-blue-600 dark:text-blue-300">{{ $companyUrl }}</code>
                                            <button type="button" data-copy="{{ $companyUrl }}"
                                                class="js-copy inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-600 dark:text-slate-300 dark:hover:border-blue-500/50">
                                                <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada perusahaan aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Endpoints --}}
            <div class="space-y-4">
                @foreach ($endpoints as $ep)
                    @php($fullUrl = $baseUrl . '/' . $sampleSlug . $ep['suffix'])
                    <div class="rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                        <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 dark:border-slate-700/60 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <span class="rounded-md bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">GET</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $ep['title'] }}</div>
                                    <code class="text-xs text-slate-500 dark:text-slate-400">/api/v1/companies{{ $ep['path'] }}</code>
                                </div>
                            </div>
                            <button type="button" data-copy="{{ $fullUrl }}"
                                class="js-copy inline-flex w-fit items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-600 dark:text-slate-300 dark:hover:border-blue-500/50">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin contoh URL
                            </button>
                        </div>
                        <div class="px-5 py-4">
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $ep['desc'] }}</p>

                            @if (!empty($ep['params']))
                                <div class="mt-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Query params</div>
                                    <div class="mt-1.5 overflow-hidden rounded-xl border border-slate-100 dark:border-slate-700/60">
                                        <table class="w-full text-xs">
                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                                @foreach ($ep['params'] as $param)
                                                    <tr>
                                                        <td class="w-48 px-3 py-2 align-top"><code class="text-blue-600 dark:text-blue-300">{{ $param[0] }}</code></td>
                                                        <td class="px-3 py-2 text-slate-500 dark:text-slate-400">{{ $param[1] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <details class="mt-3 group">
                                <summary class="flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 dark:text-slate-400">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 transition group-open:rotate-90"></i>
                                    Contoh response
                                </summary>
                                <pre class="mt-2 overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs leading-relaxed text-slate-100"><code>{{ $ep['example'] }}</code></pre>
                            </details>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Kode error & catatan --}}
            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Kode Status</h2>
                    <table class="mt-3 w-full text-sm">
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach ([
                                ['200', 'OK'],
                                ['304', 'Not Modified (ETag cocok)'],
                                ['404', 'Perusahaan/produk/kategori tidak ada, nonaktif, atau milik perusahaan lain'],
                                ['429', 'Melewati rate limit (lihat header Retry-After)'],
                                ['500', 'Kesalahan server'],
                            ] as $err)
                                <tr>
                                    <td class="w-16 py-2 align-top font-mono font-bold text-slate-700 dark:text-slate-200">{{ $err[0] }}</td>
                                    <td class="py-2 text-slate-500 dark:text-slate-400">{{ $err[1] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="mt-3 text-xs text-slate-400">Body error selalu: <code class="text-slate-500 dark:text-slate-300">{ "message": "..." }</code></p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Catatan</h2>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                        <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 w-4 h-4 shrink-0 text-emerald-500"></i> Tiap website hanya menampilkan katalog perusahaannya sendiri (ter-scope by slug).</li>
                        <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 w-4 h-4 shrink-0 text-emerald-500"></i> Harga = harga normal varian; flash sale belum diperhitungkan.</li>
                        <li class="flex gap-2"><i data-lucide="shield" class="mt-0.5 w-4 h-4 shrink-0 text-slate-400"></i> Field internal (angka stok, poin/redeem) sengaja tidak diekspos.</li>
                        <li class="flex gap-2"><i data-lucide="globe" class="mt-0.5 w-4 h-4 shrink-0 text-slate-400"></i> CORS aktif — bisa dipanggil langsung dari browser.</li>
                        <li class="flex gap-2"><i data-lucide="git-branch" class="mt-0.5 w-4 h-4 shrink-0 text-slate-400"></i> Perubahan breaking akan naik ke <code class="text-xs">/api/v2</code>.</li>
                    </ul>
                </div>
            </div>
        </main>

        <script>
            document.querySelectorAll('.js-copy').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var text = btn.getAttribute('data-copy');
                    var done = function () {
                        var original = btn.innerHTML;
                        btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i> Tersalin';
                        if (window.lucide) window.lucide.createIcons();
                        setTimeout(function () {
                            btn.innerHTML = original;
                            if (window.lucide) window.lucide.createIcons();
                        }, 1500);
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(done).catch(done);
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = text; document.body.appendChild(ta); ta.select();
                        try { document.execCommand('copy'); } catch (e) {}
                        document.body.removeChild(ta); done();
                    }
                });
            });
            if (window.lucide) window.lucide.createIcons();
        </script>
    </body>

</html>
