<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Request\PayloadStorage;
use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, HttpMethod, PreMiddleware, RoutePrefix, ClearMiddleware};
use Suphle\Response\Format\Json;
use Suphle\Auth\Middleware\PathAuthorization;
use Suphle\Tests\Mocks\Modules\ModuleOne\{Services\EmploymentEditMock, PayloadReaders\BaseEmploymentBuilder};
use Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths\{EmploymentEditRule, AdminRule};

#[RoutePrefix("/employment")]
#[PreMiddleware(PathAuthorization::class)]
class EmploymentEditCoordinator extends BaseCoordinator
{
    public function __construct(
        protected readonly EmploymentEditMock $editService,
        protected readonly PayloadStorage $payloadStorage
    ) { }

    #[Route("retain")]
    public function retain(): Json
    {
        return new Json([]);
    }

    #[Route("secede")]
    #[ClearMiddleware(PathAuthorization::class)]
    public function secede(): Json
    {
        return new Json([]);
    }

    #[Route("gmulti-edit-unauth")]
    #[ClearMiddleware(PathAuthorization::class)]
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

    #[Route("pmulti-edit/{id}", method: HttpMethod::POST)]
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

    #[Route("admin-entry")]
    #[PreMiddleware(AdminRule::class)]
    public function adminEntry(): Json
    {
        return new Json([]);
    }
}
