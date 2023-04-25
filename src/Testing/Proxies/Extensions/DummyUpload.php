<?php

namespace Suphle\Testing\Proxies\Extensions;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class DummyUpload extends UploadedFile
{
    protected $fileSize;

    public function setSize(int $fileSize): void
    {

        $this->fileSize = $fileSize * 1024;
    }

    public function getSize(): int
    {

        return $this->fileSize ?? parent::getSize();
    }
}
