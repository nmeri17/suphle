<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

use Suphle\Tests\Mocks\Modules\ModuleOne\Enums\BackedEnum;

class InjectsBackedEnum
{
    public function __construct(public readonly BackedEnum $backedEnum)
    {

        //
    }
}
