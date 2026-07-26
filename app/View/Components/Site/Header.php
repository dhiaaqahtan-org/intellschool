<?php

namespace App\View\Components\Site;

use App\Support\SiteNavigation;
use Illuminate\View\Component;

class Header extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $headerPages = SiteNavigation::primary();

        return view()->first(['components.site.custom.header', 'components.site.default.header'], compact('headerPages'));
    }
}
