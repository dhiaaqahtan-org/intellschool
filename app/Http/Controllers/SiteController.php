<?php

namespace App\Http\Controllers;

use App\Services\SiteService;

class SiteController extends Controller
{
    public function home(SiteService $service)
    {
        try {
            return $service->getPage('home');
        } catch (\Throwable $e) {
            return redirect('/app/login');
        }
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
