<?php
namespace Suphle\Contracts\Requests;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Exception\Explosives\{ValidationFailure, DevError\NoCompatibleValidator};

interface ValidationEvaluator
{
    public function getValidatorErrors(): array;

    public function validationRenderer(array $failureDetails): BaseRenderer;

    /**
     * @throws NoCompatibleValidator
    */
    public function eligibleToValidate(): bool;

    /**
     * @throws ValidationFailure
    */
    public function executeValidatorManager():void;
}
