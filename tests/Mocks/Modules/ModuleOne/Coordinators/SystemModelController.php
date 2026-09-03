<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Services\SystemModelEditMock1;

#[RoutePrefix("/sme")]
class SystemModelController extends BaseCoordinator
{
    public function __construct(protected readonly SystemModelEditMock1 $editService)
    {

        //
    }

    #[ValidationRules([])] // Empty since test doesn't require routing to this controller
    #[Route("/handle-put", HttpMethod::PUT)]
    public function handlePutRequest(object $builder):Json {
        $contents  = ["message" => "failed"];

    	if ($this->editService->updateModels($builder)) {

	        $contents = ["message" => "success"];
	    }

        return new Json($contents);
    }

    #[Route("/handle-put2", HttpMethod::PUT)]
    public function putOtherServiceMethod():Json
    {

        return new Json(["data" => $this->editService->unrelatedToUpdate()]);
    }
}
