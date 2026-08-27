<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Canary;

use Suphle\Contracts\Routing\CanaryEvaluator;

use Suphle\Request\PayloadStorage;

class CanaryRequestHasFoo implements CanaryEvaluator
{
    public const MARKER = "foo";

    public function __construct(protected readonly PayloadStorage $payloadStorage)
    {

        //
    }

    public function willLoad():?string
    {

        return $this->payloadStorage->hasKey(self::MARKER)? self::MARKER:null;
    }
}
