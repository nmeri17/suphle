<?php

namespace Suphle\Services\Structures;

use Suphle\Contracts\Requests\FileInputReader;

use Suphle\IO\Image\OptimizersManager;

use Suphle\Services\Decorators\VariableDependencies;

#[VariableDependencies([

    "setPayloadStorage", "setInputReader"
])]
abstract class ImagefulPayload extends ModellessPayload
{
    protected array $allFiles;

    public function __construct(protected OptimizersManager $imageOptimizer)
    {

        // default optimizer. can be replaced
    }

    public function setInputReader(FileInputReader $inputReader): void
    {

        $this->allFiles = $inputReader->getFileObjects();
    }
}
