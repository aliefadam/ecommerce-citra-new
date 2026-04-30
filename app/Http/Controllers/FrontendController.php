<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        return view('frontend.index');
    }

    public function kategori()
    {
        return view('frontend.kategori');
    }

    public function flashSale()
    {
        return view('frontend.flash-sale');
    }

    public function search(Request $request)
    {
        return view('frontend.search-results', [
            'query' => (string) $request->query('q', ''),
        ]);
    }

    public function detailProduk()
    {
        return view('frontend.detail-produk');
    }

    public function checkout()
    {
        return view('frontend.checkout');
    }

    public function profil()
    {
        return view('frontend.profil');
    }
}
