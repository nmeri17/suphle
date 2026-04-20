<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

use Suphle\Contracts\Services\CallInterceptors\MultiUserModelEdit;

use Suphle\Contracts\Services\Models\IntegrityModel;

use Suphle\Services\Structures\BaseErrorCatcherService;

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, DomainService};

#[InterceptsCalls(MultiUserModelEdit::class)]
#[DomainService(mutation: true)]
#[VariableDependencies([

    "setPayloadStorage", "setRouteInfo"
])]
class EmploymentEditMock implements MultiUserModelEdit
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
