<template x-teleport="body">
    <div x-show="confirmOpen" x-cloak
        x-effect="document.body.classList.toggle('overflow-hidden', confirmOpen)"
        @keydown.escape.window="if (!isSubmitting) confirmOpen = false"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        role="dialog" aria-modal="true" aria-labelledby="productConfirmTitle">
        <div class="absolute inset-0 bg-slate-950/75 backdrop-blur-sm"
            @click="if (!isSubmitting) confirmOpen = false"></div>

        <div x-show="confirmOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
            </div>

            <h3 id="productConfirmTitle" class="mb-2 text-lg font-bold text-slate-800 dark:text-white">
                {{ $title }}
            </h3>
            <p class="mb-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $message }}</p>
            <p x-show="productName" class="mb-6 truncate text-sm font-semibold text-slate-700 dark:text-slate-200"
                x-text="productName"></p>

            <div class="flex gap-3">
                <button type="button" @click="confirmOpen = false" :disabled="isSubmitting"
                    class="flex-1 cursor-pointer rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                    Batal
                </button>
                <button type="button" @click="submitConfirmed()" :disabled="isSubmitting"
                    class="inline-flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-70">
                    <span x-show="isSubmitting"
                        class="h-4 w-4 animate-spin rounded-full border-2 border-blue-200 border-t-white"></span>
                    <span x-text="isSubmitting ? 'Menyimpan...' : @js($confirmLabel)"></span>
                </button>
            </div>
        </div>
    </div>
</template>
