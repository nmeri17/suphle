<?php

namespace Suphle\Tests\Integration\Bridge\Laravel;

use Suphle\Bridge\Laravel\Routing\ModuleRouteMatcher;

use Suphle\Contracts\Config\Laravel;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\LaravelMock};

class ModuleRouteMatcherTest extends ModuleLevelTest
{
    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(
                    Laravel::class,
                    LaravelMock::class,
                    []
                );
            })
        ];
    }

    public function test_getResponse_from_provided_route()
    {

        // given ==> @see module binding

        // when
        $this->get("/laravel/entry"); // calling this before sut is created since LaravelContainer needs the information

        $sut = $this->getContainer()->getClass(ModuleRouteMatcher::class); // RegistersRouteProvider->boot never runs

        $this->assertTrue($sut->canHandleRequest()); // then
    }
}
