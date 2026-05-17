<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;

class FrontendContentController extends Controller
{
    public function page(string $slug)
    {
        $page = ContentPage::query()
            ->published()
            ->where('type', ContentPage::TYPE_PAGE)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.content-page', compact('page'));
    }

    public function blog()
    {
        $posts = ContentPage::query()
            ->published()
            ->where('type', ContentPage::TYPE_POST)
            ->latest('published_at')
            ->paginate(9);

        return view('frontend.blog-index', compact('posts'));
    }

    public function post(string $slug)
    {
        $page = ContentPage::query()
            ->published()
            ->where('type', ContentPage::TYPE_POST)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.content-page', compact('page'));
    }
}
