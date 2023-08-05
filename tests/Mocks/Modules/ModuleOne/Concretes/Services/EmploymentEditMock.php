<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

use Suphle\Contracts\Services\CallInterceptors\MultiUserModelEdit;

use Suphle\Contracts\Services\Models\IntegrityModel;

use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies};

#[InterceptsCalls(MultiUserModelEdit::class)]
#[VariableDependencies([

    "setPayloadStorage", "setPlaceholderStorage"
])]
class EmploymentEditMock extends UpdatefulService implements MultiUserModelEdit
{
    use BaseErrorCatcherService;

    public function getResource(object $builder): IntegrityModel
    {

        return $builder->first();
    }

    public function updateResource(object $builder, array $toUpdate)
    {

        return $builder->update($toUpdate);
    }
}
