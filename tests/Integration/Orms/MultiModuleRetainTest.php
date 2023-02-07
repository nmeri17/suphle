<?php
	namespace Suphle\Tests\Integration\Orms;

	use Suphle\Testing\Condiments\BaseDatabasePopulator;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class MultiModuleRetainTest extends DescriptorCollection {

		use BaseDatabasePopulator;

		protected const NUM_TO_INSERT = 1;

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		public function test_insertion_and_reading_works () {

			$currentCount = $this->getInitialCount();

			$this->assertSame(// given

				$currentCount, $this->replicator->getCount()
			);

			$this->replicator->modifyInsertion(self::NUM_TO_INSERT); // when

			$this->assertSame(

				$currentCount + self::NUM_TO_INSERT, $this->replicator->getCount()
			); // then
		}

		/** 
		 * This is different from the one on SingleModuleRetainTest cuz that didn't reveal running with multiple containers resets the connection
		 * 
		 * @depends test_insertion_and_reading_works
		*/
		public function test_will_see_leftover_from_previous_seedings () {

			$this->assertSame(

				($this->getInitialCount() * 2) + self::NUM_TO_INSERT,

				$this->replicator->getCount()
			);
		}

		/**
		 * @depends test_will_see_leftover_from_previous_seedings
		*/
		public function test_multi_module_routing_doesnt_reset_database () {

			// when
			$this->get("/segment")->assertOk(); // sanity check

			$this->assertSame(

				($this->getInitialCount() * 3) + self::NUM_TO_INSERT,

				$this->replicator->getCount()
			); // then
		}
	}
?>