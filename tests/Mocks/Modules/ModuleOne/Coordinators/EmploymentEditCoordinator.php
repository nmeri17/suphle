<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Request\PayloadStorage;
use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, HttpMethod, PreMiddleware};
use Suphle\Response\Format\Json;
use Suphle\Auth\Middleware\PathAuthorization;
use Suphle\Tests\Mocks\Modules\ModuleOne\{Concretes\Services\EmploymentEditMock, PayloadReaders\BaseEmploymentBuilder};
use Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths\{EmploymentEditRule, AdminRule};

class EmploymentEditCoordinator extends BaseCoordinator
{
    public function __construct(
        protected readonly EmploymentEditMock $editService,
        protected readonly PayloadStorage $payloadStorage
    ) {
        //
    }

    #[Route("retain")]
    #[PreMiddleware(PathAuthorization::class)]
    public function retain(): Json
    {
        return new Json([]);
    }

    #[Route("additional-rule")]
    #[PreMiddleware(PathAuthorization::class)]
    public function additionalRule(): Json
    {
        return new Json([]);
    }

    #[Route("secede")]
    public function secede(): Json
    {
        return new Json([]);
    }

    #[Route("gmulti-edit/{id}")]
    #[PreMiddleware(PathAuthorization::class)]
    public function gmultiEdit(): Json
    {
        return new Json([]);
    }

    #[Route("gmulti-edit-unauth")]
    public function gmultiEditUnauth(): Json
    {
        return new Json([]);
    }

    #[Route("gmulti-edit/{id}")]
    #[PreMiddleware(PathAuthorization::class)]
    public function getEmploymentDetails(BaseEmploymentBuilder $employmentBuilder): Json
    {
        return new Json([
            "data" => $this->editService->getResource($employmentBuilder->getBuilder())
        ]);
    }

    #[Route("pmulti-edit/{id}")]
    #[PreMiddleware(PathAuthorization::class)]
    #[ValidationRules([
        "id" => "required|numeric|exists:employment,id",
        "salary" => "numeric|min:20000"
    ])]
    public function updateEmploymentDetails(BaseEmploymentBuilder $employmentBuilder): Json
    {
        return new Json([
            "message" => $this->editService->updateResource(
                $employmentBuilder->getBuilder(), 
                $this->payloadStorage->only(["salary"])
            )
        ]);
    }
}
