<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Meta;

use Suphle\Hydration\Structures\BaseInterfaceCollection;

use Suphle\Contracts\{Events, Auth\UserContract};

use Suphle\Tests\Mocks\Modules\ModuleTwo\{ Events\AssignListeners};

use Suphle\Tests\Mocks\Interactions\ModuleTwo;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

class CustomInterfaceCollection extends BaseInterfaceCollection
{
    public function getConfigs(): array
    {
        return array_merge(parent::getConfigs(), [

            //
        ]);
    }

    public function simpleBinds(): array
    {

        return array_merge(parent::simpleBinds(), [

            ModuleTwo::class => ModuleApi::class,

            Events::class => AssignListeners::class,

            UserContract::class => EloquentUser::class
        ]);
    }
}
