<?php

namespace Suphle\Hydration\Structures;

class HydratedConcrete
{
    public function __construct(private $concrete, protected readonly string $createdFor)
    {

        //
    }

    public function getCreatedFor(): string
    {

        return $this->createdFor;
    }

    public function getConcrete()
    {

        return $this->concrete;
    }
}
