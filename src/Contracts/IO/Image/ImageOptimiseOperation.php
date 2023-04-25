<?php

namespace Suphle\Contracts\IO\Image;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageOptimiseOperation
{
    /**
     * Moving the files is prohibited so it doesn't affect subsequent operations. Implementations should only transform their own copy
     * @return string[] of file names on synchronous operations
    */
    public function getTransformed(): ?array;

    /**
     * @param {images} UploadedFile[]
    */
    public function setFiles(array $images): void;

    public function setResourceName(string $name): void;

    /**
     * @return Name of sub-folder where image will be stored e.g. images/{operationName}/{resourceName}
    */
    public function getOperationName(): string;

    public function savesAsync(): bool;

    public function getAsyncNames(): array;
}
