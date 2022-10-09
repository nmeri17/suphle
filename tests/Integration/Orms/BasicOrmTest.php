<?php
	namespace Suphle\Tests\Integration\Orms;

	use Suphle\Testing\Condiments\BaseDatabasePopulator;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class BasicOrmTest extends DescriptorCollection {

		use BaseDatabasePopulator;

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		public function test_insertion_before_read_is_visible () {

			$currentCount = $this->getInitialCount();

			$this->assertSame(// given

				$currentCount, $this->replicator->getCount()
			);

			$model = $this->replicator->modifyInsertion(1)->first(); // when

			$this->assertSame($currentCount + 1, $this->replicator->getCount()); // then
		}

		public function test_insertion_before_reset_is_lost () {

			$currentCount = $this->getInitialCount();

			$this->assertSame(// given

				$currentCount, $this->replicator->getCount()
			);

			// when
			$this->replicator->modifyInsertion(10);

			$this->get("/segment"); // since this resets connection

			// reverts to original figure before seeding occured
			$this->assertSame(0, $this->replicator->getCount()); // then
		}
	}
?>