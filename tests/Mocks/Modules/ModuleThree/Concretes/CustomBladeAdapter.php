<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Concretes;

use Suphle\Adapters\Presentation\Blade\DefaultBladeAdapter;

use Suphle\Tests\Mocks\Modules\ModuleThree\Markup\Components\AppLayouts;

class CustomBladeAdapter extends DefaultBladeAdapter
{
    public function bindComponentTags(): void
    {

        $this->bladeCompiler->component("layout", AppLayouts::class);
    }
}
