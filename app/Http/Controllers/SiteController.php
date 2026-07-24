<?php

namespace App\Http\Controllers;

use App\Services\SiteService;

class SiteController extends Controller
{
    public function home(SiteService $service)
    {
        // Designed marketing home. Inner pages stay CMS-driven via getPage().
        // To restore the CMS-controlled home: return $service->getPage('Home');
        return view('site.default.index');
    }

    public function page(string $slug, SiteService $service)
    {
        return $service->getPage($slug);
    }

    public function pageView(string $slug, SiteService $service)
    {
        return $service->getPageView($slug);
    }

    public function getEvent(string $slug, string $uuid, SiteService $service)
    {
        return $service->getEvent($slug, $uuid);
    }
}
