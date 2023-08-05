<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Request\PayloadStorage;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Concretes\Services\EmploymentEditMock, PayloadReaders\BaseEmploymentBuilder};

class EmploymentEditCoordinator extends ServiceCoordinator
{
    public function __construct(
    	protected readonly EmploymentEditMock $editService,
    	protected readonly PayloadStorage $payloadStorage
    )
    {

        //
    }

    public function simpleResult()
    {

        return [];
    }

    public function getEmploymentDetails(BaseEmploymentBuilder $employmentBuilder)
    {

        return [

            "data" => $this->editService->getResource($employmentBuilder->getBuilder())
        ];
    }

    #[ValidationRules([
        "id" => "required|numeric|exists:employment,id",

        "salary" => "numeric|min:20000"
    ])]
    public function updateEmploymentDetails(BaseEmploymentBuilder $employmentBuilder): iterable
    {

        return [

            "message" => $this->editService->updateResource(

            	$employmentBuilder->getBuilder(), $this->payloadStorage->only(["salary"])
            )
        ];
    }
}
