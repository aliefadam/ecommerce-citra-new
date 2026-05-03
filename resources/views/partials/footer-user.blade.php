<footer class="bg-slate-900 text-slate-300 mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                        </svg>
                    </div>
                    <span class="text-white font-extrabold text-xl">Ecommerce Citra</span>
                </div>
                <p class="text-sm leading-relaxed mb-4">Platform belanja online terpercaya dengan jutaan produk pilihan
                    dan pengiriman ke seluruh Indonesia.</p>
                <div class="flex gap-3">
                    <a href="#"
                        class="w-9 h-9 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="#"
                        class="w-9 h-9 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-pink-600 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg>
                    </a>
                    <a href="#"
                        class="w-9 h-9 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4">Belanja</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('frontend.kategori') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Semua Produk</a></li>
                    <li><a href="{{ route('frontend.flash-sale') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Flash Sale</a></li>
                    <li><a href="{{ route('frontend.kategori') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Produk Baru</a></li>
                    <li><a href="{{ route('frontend.kategori') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Best Seller</a></li>
                    <li><a href="{{ route('frontend.cart') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Keranjang</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4">Akun Saya</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Profil</a></li>
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Pesanan Saya</a></li>
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Wishlist</a></li>
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Ulasan</a></li>
                    <li><a href="{{ route('frontend.profil') }}"
                            class="text-sm hover:text-blue-400 transition-colors">Pengaturan</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4">Bantuan</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm hover:text-blue-400 transition-colors">Pusat Bantuan</a></li>
                    <li><a href="#" class="text-sm hover:text-blue-400 transition-colors">Kebijakan Privasi</a></li>
                    <li><a href="#" class="text-sm hover:text-blue-400 transition-colors">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="text-sm hover:text-blue-400 transition-colors">Cara Belanja</a></li>
                    <li><a href="#" class="text-sm hover:text-blue-400 transition-colors">Hubungi Kami</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-slate-500">© 2025 Ecommerce Citra. All rights reserved.</p>
            <div class="flex items-center gap-3 flex-wrap justify-center">
                <div class="bg-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-400 font-medium">Visa</div>
                <div class="bg-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-400 font-medium">Mastercard</div>
                <div class="bg-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-400 font-medium">GoPay</div>
                <div class="bg-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-400 font-medium">OVO</div>
                <div class="bg-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-400 font-medium">DANA</div>
                <div class="bg-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-400 font-medium">BCA</div>
                <div class="bg-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-400 font-medium">COD</div>
            </div>
        </div>
    </div>
</footer>
