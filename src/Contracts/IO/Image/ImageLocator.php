<?php

namespace Suphle\Contracts\IO\Image;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageLocator
{
    /**
     * No actual movement is required. We only need the names to refund caller so it can be stored in database
     *
     * @param {operationName} thumbnail|inferior
     * @param {resourceName} e.g. profile_photos
    */
    public function resolveName(
        UploadedFile $file,
        string $operationName,
        string $resourceName
    ): string;
}
