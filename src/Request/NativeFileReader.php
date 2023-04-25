<?php

namespace Suphle\Request;

use Suphle\Contracts\Requests\FileInputReader;

use Symfony\Component\HttpFoundation\{Request, File\UploadedFile};

class NativeFileReader implements FileInputReader
{
    /**
     * @return UploadedFile, Ensure that this matches what we create within file-upload tests. @see \Suphle\Testing\Condiments\FilesystemCleaner
    */
    public function getFileObjects(): array
    {

        return Request::createFromGlobals()->files->all();
    }
}
