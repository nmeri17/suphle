<?php

namespace Suphle\Flows\Structures;

class RangeContext
{
    private $parameterMax;
    private $parameterMin;

    public function __construct(?string $parameterMax = null, ?string $parameterMin = null)
    {

        $this->parameterMax = $parameterMax ?? "max";

        $this->parameterMin = $parameterMin ?? "min";
    }

    public function getParameterMax(): string
    {

        return $this->parameterMax;
    }

    public function getParameterMin(): string
    {

        return $this->parameterMin;
    }
}
