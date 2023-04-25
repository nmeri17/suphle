<?php

namespace Suphle\IO\Image\Operations;

use Suphle\Contracts\IO\Image\{ ImageThumbnailClient, ImageLocator, ThumbnailOperationHandler};

use Suphle\File\FileSystemReader;

class DefaultThumbnailHandler extends BaseOptimizeOperation implements ThumbnailOperationHandler
{
    protected string $operationName = ThumbnailOperationHandler::OPERATION_NAME;

    public function __construct(ImageThumbnailClient $client, ImageLocator $imageLocator, FileSystemReader $fileSystemReader)
    {

        $this->client = $client;

        parent::__construct($imageLocator, $fileSystemReader);
    }

    public function getTransformed(): ?array
    {

        return array_map(fn ($file) => $this->client->miniature($this->localFileCopy($file)), $this->imageObjects);
    }

    public function setDimensions(int $width, int $height): void
    {

        $this->client->setDimensions($width, $height);
    }
}
