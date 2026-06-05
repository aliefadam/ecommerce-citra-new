@extends('layouts.app')

@section('title', 'Detail Faktur Pajak')

@section('content')
    @php
        $statusClass = match ($taxInvoice->status) {
            'requested' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
            'processing' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800',
            'issued', 'sent' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800',
            'rejected' => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
            default => 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
        };
        $displayTaxpayerNumber = $canViewSensitive ? $taxInvoice->taxpayer_number : $taxInvoice->masked_taxpayer_number;
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <a href="{{ route('tax-invoices.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:underline">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Kembali ke queue
                </a>
                <h1 class="mt-3 text-2xl font-bold text-slate-800 dark:text-white">{{ $transaction->invoice_no }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $transaction->order_id }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transactions.show', $transaction) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                    <i data-lucide="receipt" class="h-4 w-4"></i>
                    Detail Transaksi
                </a>
                <a href="{{ route('invoice.show', $transaction) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-800 dark:bg-slate-800 dark:text-indigo-300 dark:hover:bg-indigo-900/20">
                    <i data-lucide="printer" class="h-4 w-4"></i>
                    Invoice
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">{{ session('warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="font-bold text-slate-800 dark:text-white">Data Wajib Pajak</h2>
                            <p class="mt-1 text-sm text-slate-500">Snapshot data dari customer untuk transaksi ini.</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($taxInvoice->status)) }}</span>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama NPWP</p>
                            <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->taxpayer_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor NPWP</p>
                            <p class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $displayTaxpayerNumber }}</p>
                            @unless ($canViewSensitive)
                                <p class="mt-1 text-xs text-slate-400">Nomor penuh membutuhkan permission sensitive.</p>
                            @endunless
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email Penerima</p>
                            <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->taxpayer_email }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Request</p>
                            <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->requested_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat NPWP</p>
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $taxInvoice->taxpayer_address }}</p>
                        </div>
                        @if ($taxInvoice->customer_note)
                            <div class="sm:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan Customer</p>
                                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $taxInvoice->customer_note }}</p>
                            </div>
                        @endif
                        @if ($taxInvoice->rejected_reason)
                            <div class="sm:col-span-2 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                                <p class="font-semibold">Alasan penolakan</p>
                                <p class="mt-1">{{ $taxInvoice->rejected_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="font-bold text-slate-800 dark:text-white">File Faktur Pajak</h2>
                            <p class="mt-1 text-sm text-slate-500">PDF disimpan private dan hanya diakses melalui route terproteksi.</p>
                        </div>
                        @if ($taxInvoice->tax_invoice_file_path)
                            <a href="{{ route('tax-invoices.download', $taxInvoice) }}" class="inline-flex w-fit items-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:bg-slate-800 dark:text-emerald-300 dark:hover:bg-emerald-900/20">
                                <i data-lucide="download" class="h-4 w-4"></i>
                                Download PDF
                            </a>
                        @endif
                    </div>

                    @if ($taxInvoice->tax_invoice_file_path)
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Faktur</p>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->tax_invoice_number ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Faktur</p>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->tax_invoice_date?->format('d M Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Diunggah Oleh</p>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->uploadedByAdmin?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Waktu Upload</p>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->issued_at?->format('d M Y H:i') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email Terakhir</p>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->sent_at?->format('d M Y H:i') ?? 'Belum dikirim' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Download Terakhir</p>
                                <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $taxInvoice->last_downloaded_at?->format('d M Y H:i') ?? 'Belum pernah' }}</p>
                            </div>
                        </div>
                        @if ($taxInvoice->email_failed_at)
                            <div class="mt-5 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                                <p class="font-semibold">Email terakhir gagal dikirim</p>
                                <p class="mt-1">{{ $taxInvoice->email_failed_at->format('d M Y H:i') }} · {{ $taxInvoice->email_failure_reason ?: 'Detail teknis disembunyikan untuk keamanan data.' }}</p>
                            </div>
                        @endif
                    @else
                        <div class="mt-5 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-700/40 dark:text-slate-400">
                            File faktur pajak belum diunggah.
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="font-bold text-slate-800 dark:text-white">Produk</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($transaction->details as $detail)
                            <div class="flex items-start gap-3 rounded-xl border border-slate-100 p-3 dark:border-slate-700">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $detail->product_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $detail->variant_name ?: '-' }}</p>
                                </div>
                                <div class="text-right text-sm">
                                    <p class="text-slate-500">{{ $detail->quantity }} x Rp {{ number_format((int) $detail->price, 0, ',', '.') }}</p>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">Rp {{ number_format((int) $detail->subtotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="font-bold text-slate-800 dark:text-white">Histori Audit Faktur Pajak</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($statusHistories as $history)
                            <div class="flex gap-3">
                                <div class="mt-1.5 h-2.5 w-2.5 rounded-full bg-blue-500"></div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                        {{ str_replace('tax_invoice_', '', (string) $history->type) }} · {{ $history->from_status ?: '-' }} &rarr; {{ $history->to_status }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $history->created_at?->format('d M Y H:i') }} oleh {{ $history->user?->name ?? 'System' }}</p>
                                    @if ($history->note)
                                        <p class="mt-1 text-xs text-slate-500">{{ $history->note }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Belum ada perubahan status oleh admin.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="font-bold text-slate-800 dark:text-white">Ringkasan Transaksi</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-slate-400">Customer</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $transaction->customerDisplayName() }}</p>
                            <p class="text-xs text-slate-500">{{ $transaction->customerDisplayEmail() }}</p>
                        </div>
                        <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-semibold">Rp {{ number_format((int) $transaction->subtotal_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Taxable</span><span class="font-semibold">Rp {{ number_format((int) $transaction->taxable_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between {{ (int) $transaction->tax_amount > 0 ? '' : 'text-amber-600' }}"><span>{{ $transaction->tax_name ?: 'PPN' }}</span><span class="font-semibold">Rp {{ number_format((int) $transaction->tax_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Ongkir</span><span class="font-semibold">Rp {{ number_format((int) $transaction->shipping_cost, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between border-t border-slate-100 pt-3 text-base font-bold text-blue-600 dark:border-slate-700"><span>Total</span><span>Rp {{ number_format((int) $transaction->grand_total, 0, ',', '.') }}</span></div>
                    </div>
                    @if ((int) $transaction->tax_amount <= 0)
                        <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                            Transaksi ini tidak memiliki nilai PPN. Admin perlu validasi manual sebelum menerbitkan faktur pajak.
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="font-bold text-slate-800 dark:text-white">Aksi Finance</h2>
                    <div class="mt-4 space-y-3">
                        @if (auth()->user()?->hasAdminPermission('tax_invoices.process') && in_array($taxInvoice->status, ['requested', 'rejected'], true))
                            <form method="POST" action="{{ route('tax-invoices.process', $taxInvoice) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                    <i data-lucide="loader" class="h-4 w-4"></i>
                                    Tandai Processing
                                </button>
                            </form>
                        @endif

                        @if (auth()->user()?->hasAdminPermission('tax_invoices.reject') && ! in_array($taxInvoice->status, ['issued', 'sent'], true))
                            <form method="POST" action="{{ route('tax-invoices.reject', $taxInvoice) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <textarea name="rejected_reason" rows="4" required placeholder="Alasan penolakan"
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-red-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">{{ old('rejected_reason') }}</textarea>
                                <textarea name="admin_note" rows="3" placeholder="Catatan admin opsional"
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-red-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">{{ old('admin_note', $taxInvoice->admin_note) }}</textarea>
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-900/20">
                                    <i data-lucide="x-circle" class="h-4 w-4"></i>
                                    Tolak Request
                                </button>
                            </form>
                        @endif

                        @if (auth()->user()?->hasAdminPermission('tax_invoices.upload'))
                            <form method="POST" action="{{ route('tax-invoices.upload', $taxInvoice) }}" enctype="multipart/form-data" class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-700/40">
                                @csrf
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-500 dark:text-slate-300">Upload PDF Faktur Pajak</label>
                                    <input type="file" name="tax_invoice_file" accept="application/pdf" required
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                </div>
                                <input type="text" name="tax_invoice_number" value="{{ old('tax_invoice_number', $taxInvoice->tax_invoice_number) }}" placeholder="Nomor faktur pajak"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                <input type="date" name="tax_invoice_date" value="{{ old('tax_invoice_date', $taxInvoice->tax_invoice_date?->format('Y-m-d')) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                <textarea name="admin_note" rows="3" placeholder="Catatan admin opsional"
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">{{ old('admin_note', $taxInvoice->admin_note) }}</textarea>
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                    <input type="checkbox" name="send_email" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    Kirim email setelah upload
                                </label>
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                    <i data-lucide="upload-cloud" class="h-4 w-4"></i>
                                    {{ $taxInvoice->tax_invoice_file_path ? 'Ganti PDF Faktur' : 'Upload PDF Faktur' }}
                                </button>
                            </form>
                        @endif

                        @if ($taxInvoice->tax_invoice_file_path)
                            <a href="{{ route('tax-invoices.download', $taxInvoice) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 px-4 py-2.5 text-sm font-semibold text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-300 dark:hover:bg-emerald-900/20">
                                <i data-lucide="download" class="h-4 w-4"></i>
                                Download File
                            </a>
                        @endif

                        @if (auth()->user()?->hasAdminPermission('tax_invoices.send') && $taxInvoice->tax_invoice_file_path)
                            <form method="POST" action="{{ route('tax-invoices.send', $taxInvoice) }}">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200 px-4 py-2.5 text-sm font-semibold text-blue-600 hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-900/20">
                                    <i data-lucide="mail" class="h-4 w-4"></i>
                                    {{ $taxInvoice->sent_at ? 'Kirim Ulang Email' : 'Kirim Email' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </main>
@endsection
