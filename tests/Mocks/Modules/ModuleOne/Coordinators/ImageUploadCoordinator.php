<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\ImagePayloadReader;

#[RoutePrefix('')]
class ImageUploadCoordinator extends ServiceCoordinator
{
    #[Route("apply-all", HttpMethod::POST)]
    #[ValidationRules([
        "belonging_resource" => "required|string",
        "profile_pic" => "required|image"
    ])]
    public function applyAllOptimizations(ImagePayloadReader $payload): Json
    {
        $fileNames = $payload->getDomainObject() // since no computation happens, it's safe to use without checking for null
            ->inferior(150) // in the test, assert that resulting file is <= this size
            ->thumbnail(50, 50)->savedImageNames();

        return new Json($fileNames);
    }

    #[Route("apply-none", HttpMethod::POST)]
    #[ValidationRules([
        "belonging_resource" => "required|string",
        "profile_pic" => "required|image"
    ])]
    public function applyNoOptimization(ImagePayloadReader $payload): Json
    {
        return new Json($payload->getDomainObject()->savedImageNames());
    }

    #[Route("apply-crop", HttpMethod::POST)]
    #[ValidationRules([
        "belonging_resource" => "required|string",
        "profile_pic" => "required|image"
    ])]
    public function applyThumbnail(ImagePayloadReader $payload): Json
    {
        return new Json($payload->getDomainObject()->thumbnail(50, 50)->savedImageNames());
    }
}
