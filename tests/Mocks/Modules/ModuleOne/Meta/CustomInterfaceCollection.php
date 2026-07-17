<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Meta;

use Suphle\Hydration\Structures\BaseInterfaceCollection;

use Suphle\Contracts\Config\{ Laravel, Flows, Database};

use Suphle\Contracts\{Events, Auth\UserContract, Presentation\HtmlParser};

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\{ LaravelMock, FlowMock, DatabaseMock};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Events\AssignListeners, Concretes\CustomBladeAdapter};

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

class CustomInterfaceCollection extends BaseInterfaceCollection
{
    public function getConfigs(): array
    {
        return array_merge(parent::getConfigs(), [

            Flows::class => FlowMock::class,

            Laravel::class => LaravelMock::class,

            Database::class => DatabaseMock::class
        ]);
    }

    public function simpleBinds(): array
    {

        return array_merge(parent::simpleBinds(), [

            ModuleOne::class => ModuleApi::class,

            Events::class => AssignListeners::class,

            HtmlParser::class => CustomBladeAdapter::class,

            UserContract::class => EloquentUser::class
        ]);
    }
}
