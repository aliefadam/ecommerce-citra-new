<div x-show="confirmOpen" x-cloak
    @keydown.escape.window="if (!isSubmitting) confirmOpen = false"
    class="fixed inset-0 z-[200] flex items-center justify-center p-4"
    role="dialog" aria-modal="true" aria-labelledby="productConfirmTitle">
    <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
        @click="if (!isSubmitting) confirmOpen = false"></div>

    <div x-show="confirmOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800">
        <div class="p-6">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
            </div>
            <h3 id="productConfirmTitle" class="text-lg font-bold text-slate-800 dark:text-white">
                {{ $title }}
            </h3>
            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                {{ $message }}
                <span x-show="productName" class="font-semibold text-slate-700 dark:text-slate-200"
                    x-text="`&quot;${productName}&quot;`"></span>
            </p>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/80 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/30">
            <button type="button" @click="confirmOpen = false" :disabled="isSubmitting"
                class="cursor-pointer rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                Batal
            </button>
            <button type="button" @click="submitConfirmed()" :disabled="isSubmitting"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-70">
                <svg x-show="isSubmitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
                </svg>
                <span x-text="isSubmitting ? 'Menyimpan...' : @js($confirmLabel)"></span>
            </button>
        </div>
    </div>
</div>
