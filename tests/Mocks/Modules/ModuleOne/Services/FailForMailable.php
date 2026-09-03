<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Services;

use Suphle\Exception\ShutdownAlerters\MailBuildAlerter;

class FailForMailable
{
    public function __construct(protected readonly MailBuildAlerter $dependency)
    {

        //
    }
}
