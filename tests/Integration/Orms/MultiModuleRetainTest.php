<?php

namespace Suphle\Tests\Integration\Orms;

use Suphle\Testing\Condiments\BaseDatabasePopulator;

use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

use Suphle\Tests\Mocks\Models\Eloquent\Employment;

class MultiModuleRetainTest extends DescriptorCollection
{
    use BaseDatabasePopulator;

    protected function getActiveEntity(): string
    {

        return Employment::class;
    }

    public function test_can_read_inserted_data(): int
    {

        $amountMetAndSeeded = $this->replicator->getCount();

        $numToInsert = 1;

        $this->replicator->modifyInsertion($numToInsert); // when

        $expectedSize = $amountMetAndSeeded + $numToInsert;

        $this->assertSame($expectedSize, $this->replicator->getCount()); // then

        return $expectedSize;
    }

    /**
     * This is different from the one on SingleModuleRetainTest cuz that didn't reveal running with multiple containers resets the connection
     *
     * @depends test_can_read_inserted_data
    */
    public function test_will_see_leftover_from_previous_seedings(int $amountAdded): int
    {

        $expectedAdded = $this->getInitialCount() + $amountAdded;

        $this->assertSame($expectedAdded, $this->replicator->getCount());

        return $expectedAdded;
    }

    /**
     * @depends test_will_see_leftover_from_previous_seedings
    */
    public function test_multi_module_routing_doesnt_reset_database(int $amountAdded)
    {

        $expectedAdded = $this->getInitialCount() + $amountAdded;

        // when
        $this->get("/module-three/5")->assertOk(); // sanity check

        $this->assertSame($expectedAdded, $this->replicator->getCount()); // then
    }
}
