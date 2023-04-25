<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\SystemModelEditMock1;

use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\{BaseEmploymentBuilder, EmploymentId2Builder};

class HotwireCoordinator extends ServiceCoordinator
{
    public function __construct(protected SystemModelEditMock1 $editService)
    {

        //
    }

    public function loadForm(): array
    {

        return [];
    }

    #[ValidationRules([

        "id" => "required|numeric|exists:employment",

        "id2" => "required|numeric|exists:employment,id"
    ])]
    public function regularFormResponse(): array
    {

        return []; // not really necessary to return anything since they just redirect
    }

    #[ValidationRules([

        "id" => "required|numeric|exists:employment",

        "id2" => "required|numeric|exists:employment,id"
    ])]
    public function hotwireFormResponse(BaseEmploymentBuilder $employmentBuilder): array
    {

        return [];
    }

    /**
     * Just return the posted data
    */
    #[ValidationRules([

        "id" => "required|numeric|exists:employment",

        "id2" => "required|numeric|exists:employment,id"
    ])]
    public function hotwireReplace(BaseEmploymentBuilder $employmentBuilder): array
    {

        return ["data" => $employmentBuilder->getBuilder()->first()];
    }

    #[ValidationRules([])]
    public function hotwireBefore(EmploymentId2Builder $employmentBuilder): array
    {

        return ["data" => $employmentBuilder->getBuilder()->first()];
    }

    #[ValidationRules([])]
    public function hotwireAfter(BaseEmploymentBuilder $employmentBuilder): array
    {

        return ["data" => $employmentBuilder->getBuilder()->first()];
    }

    #[ValidationRules([

        "id" => "required|numeric|exists:employment",

        "id2" => "required|numeric|exists:employment,id"
    ])]
    public function hotwireUpdate(EmploymentId2Builder $employmentBuilder): array
    {

        return ["data" => $employmentBuilder->getBuilder()->first()];
    }

    #[ValidationRules([

        "id" => "required|numeric|exists:employment"
    ])]
    public function hotwireDelete(BaseEmploymentBuilder $employmentBuilder): array
    {

        return ["data" => $employmentBuilder->getBuilder()->first()]; // even though it's not used on the front end, it's still necessary for generating the target on the route collection
    }
}
