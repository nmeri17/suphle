<?php
	namespace Tilwa\Tests\Integration\Services\Jobs;

	use Tilwa\Contracts\{Database\Orm, Services\Models\IntegrityModel};

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\MockFacilitator};

	class UserEditFieldUpdateTest extends IsolatedComponentTest {

		use MockFacilitator;

		public function test_sets_identifier_inside_transaction () {

			// given
			$orm = $this->prophesize(Orm::class);

			$modelInstance = $this->positiveStub(IntegrityModel::class);

			$identifier = 55;

			$orm->runTransaction()->shouldBeCalled(); // then 1

			(new UserEditFieldUpdate(
				
				$orm->reveal(), $modelInstance, $identifier
			))->handle(); // when

			$this->assertSame($modelInstance->getEditIntegrity(), $identifier); // then 2
		}
	}
?>