<?php

namespace App\Http\Controllers;

class BackendController extends Controller
{
    public function index()
    {
        return view('backend.index');
    }

    public function charts()
    {
        return view('backend.charts');
    }

    public function components()
    {
        return view('backend.components');
    }

    public function datatables()
    {
        return view('backend.datatables');
    }

    public function settings()
    {
        return view('backend.settings');
    }
}
