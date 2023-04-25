<?php

namespace Suphle\Tests\Integration\Modules;

use Suphle\Modules\{ModuleToRoute, ModuleInitializer};

use Suphle\Contracts\Modules\DescriptorInterface;

use Suphle\Hydration\Container;

use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

class ModuleToRouteTest extends DescriptorCollection
{
    protected function moduleDidFindRoute(DescriptorInterface $descriptor): ?ModuleInitializer
    {

        return $descriptor->getContainer()->getClass(ModuleToRoute::class)

        ->findContext($this->modules); // given
    }

    protected function getModules(): array
    {

        return [ $this->moduleOne, $this->moduleThree, $this->moduleTwo ];
    }

    public function test_can_find_in_module_other_than_first()
    {

        $this->get("/module-two/5"); // when

        $this->assertNotNull($this->moduleDidFindRoute($this->moduleTwo)); // then
    }

    public function test_none_will_be_found()
    {

        $this->get("/non-existent/32"); // when

        $this->assertNull($this->moduleDidFindRoute($this->moduleTwo)); // then
    }
}
