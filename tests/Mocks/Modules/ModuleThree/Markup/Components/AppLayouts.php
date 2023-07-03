<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Markup\Components;

use Illuminate\View\Component;

class AppLayouts extends Component
{
    public function __construct(public string $pageTitle = "Suphle", public string $scripts = "")
    {

        //
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {

        return view("layouts.app");
    }
}
