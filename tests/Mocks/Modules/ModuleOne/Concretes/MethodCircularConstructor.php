<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

class MethodCircularConstructor
{
    public function __construct(protected readonly MethodCircularContainer $dependency)
    {

        //
    }
}
