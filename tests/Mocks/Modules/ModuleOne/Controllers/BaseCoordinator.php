<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Controllers;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Routing\HttpMethod;
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleOne\{PayloadReaders\ImagePayloadReader, Concretes\ARequiresBCounter};

class BaseCoordinator extends ServiceCoordinator
{
    #[Route("", HttpMethod::GET)]
    public function indexHandler(): Json
    {
        return new Json(["message" => "Hello World!"]);
    }

    #[Route("segment", HttpMethod::GET)]
    public function plainSegment(): Json
    {
        return new Json(["message" => "plain Segment"]);
    }

    #[Route("segment/{id}", HttpMethod::GET)]
    public function simplePair(): Json
    {
        return new Json(["message" => "simple pair"]);
    }

    #[Route("segment-segment/{id}", HttpMethod::GET)]
    public function hyphenatedSegments(): Json
    {
        return new Json(["message" => "hyphenated segments"]);
    }

    #[Route("segment_segment/{id}", HttpMethod::GET)]
    public function underscoredSegments(): Json
    {
        return new Json(["message" => "underscored segments"]);
    }

    #[Route("segment/{id}/segment/{id2}", HttpMethod::GET)]
    public function optionalPlaceholder(): Json
    {
        return new Json(["message" => "optional placeholder"]);
    }

    public function incorrectActionInjection(ImagePayloadReader $payload, ARequiresBCounter $aRequires): Json
    {
        return new Json([]);
    }
} 