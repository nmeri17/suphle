<?php

namespace Suphle\Tests\Integration\Exception;

use Suphle\Hydration\Container;

use Suphle\Exception\Explosives\NotFoundException;

use Suphle\Request\PayloadStorage;

use Suphle\Response\Format\Markup;

use Suphle\Contracts\{Config\ExceptionInterceptor, Modules\DescriptorInterface};

use Suphle\Testing\TestTypes\InvestigateSystemCrash;

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Exception;

class TerminatedRequestTest extends InvestigateSystemCrash
{
    protected function getModule(): DescriptorInterface
    {

        return new ModuleOneDescriptor(new Container());
    }

    public function test_exceptions_uses_assigned_handler()
    {
        $url = "/non-existent";

        $this->get($url); // given // just to populate requestDetails

        $this->assertExceptionFlushesData( // then

            ["message" => $url . " Not Found"],
            function (): never {

                throw new NotFoundException(); // when
            }
        );
    }

    public function test_exceptions_without_assigned_handler_uses_default()
    {

        $this->assertExceptionFlushesData( // then

            ["exception" => Exception::class],
            function (): never {

                throw new Exception(); // when
            }
        );
    }
}
