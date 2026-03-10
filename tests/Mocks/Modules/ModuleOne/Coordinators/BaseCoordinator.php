<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\{PayloadReaders\ImagePayloadReader, Concretes\ARequiresBCounter};

#[RoutePrefix('')]
class BaseCoordinator extends ServiceCoordinator
{
    #[Route("")]
    public function indexHandler(): Json
    {
        return new Json(["message" => "Hello World!"]);
    }

    #[Route("segment")]
    public function plainSegment(): Json
    {
        return new Json(["message" => "plain Segment"]);
    }

    #[Route("segment/{id}")]
    public function simplePair(): Json
    {
        return new Json([]);
    }

    #[Route("segment-segment/{id}")]
    public function hyphenatedSegments(): Json
    {
        return new Json([]);
    }

    #[Route("segment_segment/{id}")]
    public function underscoredSegments(): Json
    {
        return new Json([]);
    }

    #[Route("segment/{id}/segment/{id2}")]
    public function multiPlaceholders(): Json
    {
        return new Json([]);
    }

    #[Route("incorrect-action", method: HttpMethod::POST)]
    public function incorrectActionInjection(ImagePayloadReader $payload, ARequiresBCounter $aRequires): Json
    {
        return new Json([]);
    }

    #[Route("no-tag")]
    public function noTag(): Json
    {
        return new Json([]);
    }

    #[Route("first-single")]
    public function firstSingle(): Json
    {
        return new Json([]);
    }

    #[Route("fourth-single")]
    public function fourthSingle(): Json
    {
        return new Json([]);
    }

    #[Route("fifth-single")]
    public function fifthSingle(): Json
    {
        return new Json([]);
    }

    #[Route("negotiate")]
    public function negotiate(): Json
    {
        return new Json([]);
    }

    #[Route("first-untag")]
    public function firstUntag(): Json
    {
        return new Json([]);
    }

    #[Route("second-untag")]
    public function secondUntag(): Json
    {
        return new Json([]);
    }

    #[Route("third-untag")]
    public function thirdUntag(): Json
    {
        return new Json([]);
    }

    #[Route("retain")]
    public function retain(): Json
    {
        return new Json([]);
    }

    #[Route("additional-tag")]
    public function additionalTag(): Json
    {
        return new Json([]);
    }

    #[Route("segment/{id}")]
    public function segmentId(): Json
    {
        return new Json([]);
    }

    #[Route("segment/{id}/segment/{id2}")]
    public function segmentIdSegmentId2(): Json
    {
        return new Json([]);
    }

    #[Route("admin-entry")]
    public function adminEntry(): Json
    {
        return new Json([]);
    }
}
