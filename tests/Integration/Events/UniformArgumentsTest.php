<?php

namespace Suphle\Tests\Integration\Events;

use Suphle\Contracts\Events;

use Suphle\Testing\Proxies\WriteOnlyContainer;

use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\EmitterAsListener};

use InvalidArgumentException;

class UniformArgumentsTest extends EventTestCreator
{
    protected function setModuleOne(): void
    {

        $this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

            $container->replaceWithMock(Events::class, EmitterAsListener::class, []);
        });
    }

    public function test_cant_listen_on_emitter()
    {

        // given => see module injection

        $this->expectException(InvalidArgumentException::class);// then

        $this->parentSetUp(); // when
    }
}
