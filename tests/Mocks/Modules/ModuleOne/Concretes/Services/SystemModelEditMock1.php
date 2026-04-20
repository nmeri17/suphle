<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

use Suphle\Services\Structures\BaseErrorCatcherService;

use Suphle\Contracts\Services\CallInterceptors\SystemModelEdit;

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, DomainService};

#[InterceptsCalls(SystemModelEdit::class)]
#[VariableDependencies([

    "setPayloadStorage", "setRouteInfo"
])]
#[DomainService(mutation: true)]
class SystemModelEditMock1 implements SystemModelEdit
{
    use BaseErrorCatcherService;

    public function updateModels(object $baseModel):bool
    {

        return true;
    }

    public function modelsToUpdate (object $baseModel): array
    {

        return [$baseModel];
    }

    public function unrelatedToUpdate()
    {
    }
}
