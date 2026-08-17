<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Canary;

use Suphle\Contracts\{Routing\CanaryEvaluator, Auth\AuthStorage};

class CanaryForUser5 implements CanaryEvaluator
{
    public const MARKER = "user-5";
    
    public function __construct(protected readonly AuthStorage $authStorage) {}

    public function willLoad():?string
    {

        return $this->authStorage->getId() == 5? self::MARKER: null;
    }
}
