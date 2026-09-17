<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Meta;

use Suphle\Hydration\Structures\BaseInterfaceCollection;

use Suphle\Contracts\Config\{ Flows, Database};

use Suphle\Contracts\{ Auth\UserContract, Presentation\HtmlParser};

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\{ FlowMock, DatabaseMock};

use Suphle\Tests\Mocks\Modules\ModuleOne\{ Concretes\CustomBladeAdapter};

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

class CustomInterfaceCollection extends BaseInterfaceCollection
{
    public function getConfigs(): array
    {
        return array_merge(parent::getConfigs(), [

            Flows::class => FlowMock::class,

            Database::class => DatabaseMock::class
        ]);
    }

    public function simpleBinds(): array
    {

        return array_merge(parent::simpleBinds(), [

            ModuleOne::class => ModuleApi::class,

            HtmlParser::class => CustomBladeAdapter::class,

            UserContract::class => EloquentUser::class
        ]);
    }
}
