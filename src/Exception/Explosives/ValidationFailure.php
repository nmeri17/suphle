<?php

namespace Suphle\Exception\Explosives;

use Suphle\Contracts\Requests\ValidationEvaluator;

use Exception;

class ValidationFailure extends Exception
{
    public function __construct(protected readonly ValidationEvaluator $evaluator)
    {

        $this->message = json_encode(
            $evaluator->getValidatorErrors(),
            JSON_PRETTY_PRINT
        ); // assigning here otherwise assertion failure will preclude seeing what failed
    }

    public function getEvaluator(): ValidationEvaluator
    {

        return $this->evaluator;
    }
}
