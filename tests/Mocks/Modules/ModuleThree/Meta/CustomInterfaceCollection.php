<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Meta;

use Suphle\Hydration\Structures\BaseInterfaceCollection;

use Suphle\Contracts\Config\{ Flows};

use Suphle\Contracts\{ Auth\UserContract, Presentation\HtmlParser};

use Suphle\Tests\Mocks\Modules\ModuleThree\Config\{ FlowMock};

use Suphle\Tests\Mocks\Interactions\ModuleThree;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

class CustomInterfaceCollection extends BaseInterfaceCollection
{
    public function getConfigs(): array
    {
        return array_merge(parent::getConfigs(), [

            Flows::class => FlowMock::class
        ]);
    }

    public function simpleBinds(): array
    {

        return array_merge(parent::simpleBinds(), [

            ModuleThree::class => ModuleApi::class,

            UserContract::class => EloquentUser::class
        ]);
    }
}
