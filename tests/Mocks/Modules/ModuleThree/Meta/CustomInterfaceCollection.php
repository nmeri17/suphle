<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Meta;

use Suphle\Hydration\Structures\BaseInterfaceCollection;

use Suphle\Contracts\Config\{Router, Flows};

use Suphle\Contracts\{Events, Auth\UserContract};

use Suphle\Tests\Mocks\Modules\ModuleThree\Config\{RouterMock, FlowMock};

use Suphle\Tests\Mocks\Modules\ModuleThree\Events\AssignListeners;

use Suphle\Tests\Mocks\Interactions\ModuleThree;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

class CustomInterfaceCollection extends BaseInterfaceCollection
{
    public function getConfigs(): array
    {

        return array_merge(parent::getConfigs(), [

            Flows::class => FlowMock::class,

            Router::class => RouterMock::class
        ]);
    }

    public function simpleBinds(): array
    {

        return array_merge(parent::simpleBinds(), [

            ModuleThree::class => ModuleApi::class,

            Events::class => AssignListeners::class,

            UserContract::class => EloquentUser::class
        ]);
    }
}
