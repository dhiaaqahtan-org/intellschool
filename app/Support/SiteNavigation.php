<?php

namespace App\Support;

use App\Models\Site\Page;
use Illuminate\Support\Collection;

class SiteNavigation
{
    private const PRIMARY_ORDER = [
        'home',
        'about',
        'academics',
        'admissions',
        'campus-life',
        'news',
        'contact',
    ];

    /**
     * Build the public navigation directly from public CMS pages.
     *
     * Known school pages follow the approved design order. Any additional
     * public page created by an administrator is appended automatically.
     */
    public static function primary(): Collection
    {
        $pages = Page::query()
            ->where('seo->is_public', true)
            ->get();

        return $pages
            ->sortBy(function (Page $page) {
                $position = array_search($page->slug, self::PRIMARY_ORDER, true);

                return $position === false ? 1000 + $page->id : $position;
            })
            ->values();
    }
}
