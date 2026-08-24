<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator as SuphleController;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\{PayloadReaders\ImagePayloadReader, Concretes\ARequiresBCounter};

#[RoutePrefix('')]
class BaseCoordinator extends SuphleController
{
    #[Route("")]
    public function indexHandler(): Json
    {
        return new Json(["message" => "Hello World!"]);
    }

    #[Route("/segment")]
    public function plainSegment(): Json
    {
        return new Json(["message" => "plain Segment"]);
    }

    #[Route("/segment/{id}")]
    public function simplePair(): Json
    {
        return new Json([]);
    }

    #[Route("/segment-segment/{id}")]
    public function hyphenatedSegments(): Json
    {
        return new Json([]);
    }

    #[Route("/segment_segment/{id}")]
    public function underscoredSegments(): Json
    {
        return new Json([]);
    }

    #[Route("/segment/{id}/segment/{id2}")]
    public function multiPlaceholders(): Json
    {
        return new Json([]);
    }

    #[Route("incorrect-action", method: HttpMethod::POST)]
    public function incorrectActionInjection(ImagePayloadReader $payload, ARequiresBCounter $aRequires): Json
    {
        return new Json([]);
    }

    #[Route('/test-method/{id}', method: HttpMethod::PUT)]
    public function update(): Json
    {
        return new Json(['status' => 'updated', 'id' => 5]);
    }

    #[Route('/test-method/{id}', method: HttpMethod::DELETE)]
    public function destroy(): Json
    {
        return new Json(['status' => 'deleted', 'id' => 5]);
    }
}
