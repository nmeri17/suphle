<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

use Suphle\Contracts\Services\CallInterceptors\SystemModelEdit;

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies};

#[InterceptsCalls(SystemModelEdit::class)]
#[VariableDependencies([

    "setPayloadStorage", "setPlaceholderStorage"
])]
class SystemModelEditMock1 extends UpdatefulService implements SystemModelEdit
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
