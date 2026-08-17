<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $contents = Content::query()
            ->with('media')
            ->published()
            ->whereIn('type', ['legal', 'myth', 'faq', 'story'])
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('type');

        return view('home', [
            'legalContents' => $contents->get('legal', collect()),
            'myths' => $contents->get('myth', collect()),
            'faqs' => $contents->get('faq', collect()),
            'stories' => $contents->get('story', collect()),
        ]);
    }
}
