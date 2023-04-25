<?php

namespace Suphle\Services\Structures;

trait SetsReflectionAttributes
{
    protected array $attributesList = [];

    public function setAttributesList(array $attributes): void
    {

        $this->attributesList = $attributes;
    }
}
