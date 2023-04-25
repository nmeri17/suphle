<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\ImagePayloadReader;

class ImageUploadCoordinator extends ServiceCoordinator
{
    #[ValidationRules([
        "belonging_resource" => "required|string",

        "profile_pic" => "required|image"
    ])]
    public function applyAllOptimizations(ImagePayloadReader $payload): array
    {

        $fileNames = $payload->getDomainObject() // since no computation happens, it's safe to use without checking for null

        ->inferior(150) // in the test, assert that resulting file is <= this size
        ->thumbnail(50, 50)->savedImageNames();

        return $fileNames;
    }

    #[ValidationRules([
        "belonging_resource" => "required|string",

        "profile_pic" => "required|image"
    ])]
    public function applyNoOptimization(ImagePayloadReader $payload): array
    {

        return $payload->getDomainObject()->savedImageNames();
    }

    #[ValidationRules([
        "belonging_resource" => "required|string",

        "profile_pic" => "required|image"
    ])]
    public function applyThumbnail(ImagePayloadReader $payload): array
    {

        return $payload->getDomainObject()->thumbnail(50, 50)

        ->savedImageNames();
    }
}
