@extends('layouts.app')

@section('title', 'Owner Overview')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Report Center</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Owner Overview</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ringkasan eksekutif untuk melihat kesehatan ecommerce secara cepat.</p>
            </div>
            <form class="flex flex-wrap gap-2">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm dark:text-slate-200">
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            @foreach ([
                ['Omzet', 'Rp ' . number_format($overview['revenue'], 0, ',', '.'), $overview['revenue_growth'], 'text-blue-600'],
                ['Order Paid', number_format($overview['orders'], 0, ',', '.'), $overview['order_growth'], 'text-emerald-600'],
                ['AOV', 'Rp ' . number_format($overview['aov'], 0, ',', '.'), null, 'text-violet-600'],
                ['Item Terjual', number_format($overview['items_sold'], 0, ',', '.'), null, 'text-orange-600'],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $card[0] }}</p>
                    <p class="mt-2 text-2xl font-extrabold {{ $card[3] }}">{{ $card[1] }}</p>
                    @if (!is_null($card[2]))
                        <p class="mt-2 text-xs font-semibold {{ $card[2] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $card[2] >= 0 ? '+' : '' }}{{ $card[2] }}% dari periode sebelumnya
                        </p>
                    @else
                        <p class="mt-2 text-xs text-slate-400">Periode berjalan</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            @foreach ([
                ['Customer Baru', $overview['new_customers'], 'users'],
                ['Pembeli Aktif', $overview['active_customers'], 'user-check'],
                ['Nilai Stok', 'Rp ' . number_format($overview['inventory_value'], 0, ',', '.'), 'warehouse'],
                ['Low Stock', $overview['low_stock'], 'triangle-alert'],
                ['Pending Payment', $overview['pending_payment'], 'credit-card'],
                ['Queue Kirim', $overview['fulfillment_queue'], 'truck'],
                ['Return Aktif', $overview['return_open'], 'rotate-ccw'],
                ['Total Diskon', 'Rp ' . number_format($overview['discount'], 0, ',', '.'), 'badge-percent'],
            ] as $card)
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i data-lucide="{{ $card[2] }}" class="h-5 w-5"></i></span>
                    <div>
                        <p class="text-xs text-slate-400">{{ $card[0] }}</p>
                        <p class="font-extrabold text-slate-800 dark:text-white">{{ is_numeric($card[1]) ? number_format($card[1], 0, ',', '.') : $card[1] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="xl:col-span-2 rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Trend Omzet Harian</div>
                <div class="p-5 space-y-3">
                    @forelse ($dailyRevenue as $day)
                        @php $width = $overview['revenue'] > 0 ? max(6, ($day->total_revenue / $overview['revenue']) * 100) : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ \Illuminate\Support\Carbon::parse($day->sales_date)->format('d M Y') }}</span>
                                <span class="text-slate-500">Rp {{ number_format($day->total_revenue, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100 dark:bg-slate-700"><div class="h-2 rounded-full bg-blue-600" style="width: {{ $width }}%"></div></div>
                        </div>
                    @empty
                        <p class="py-10 text-center text-sm text-slate-400">Belum ada omzet pada periode ini.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Queue Prioritas</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($workQueue as $tx)
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-800 dark:text-white">{{ $tx->invoice_no }}</p>
                                <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">{{ $tx->status }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $tx->user?->name ?? 'Guest' }} · Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Tidak ada queue aktif.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Produk Penyumbang Omzet</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($topProducts as $product)
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-white">{{ $product->product_name }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($product->total_qty, 0, ',', '.') }} item</p>
                            </div>
                            <p class="font-extrabold text-blue-600">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada data.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-extrabold text-slate-800 dark:text-white">Status Order</div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($statusBreakdown as $status)
                        <div class="p-4 flex items-center justify-between">
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ ucfirst(str_replace('_', ' ', $status->status_key ?: 'unknown')) }}</span>
                            <span class="font-extrabold text-slate-800 dark:text-white">{{ number_format($status->total_orders, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada data.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
@endsection
