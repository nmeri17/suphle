<?php
	namespace Tilwa\Tests\Integration\Services\Jobs;

	use Tilwa\Contracts\{Database\OrmDialect, Services\Models\IntegrityModel};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class UserEditFieldUpdateTest extends IsolatedComponentTest {

		public function test_sets_identifier_inside_transaction () {

			$ormDialect = $this->positiveDouble(OrmDialect::class, [], [

				"runTransaction" => [1, []]
			]);

			$modelInstance = $this->positiveDouble(IntegrityModel::class);

			// given
			$identifier = 55;

			(new UserEditFieldUpdate(

				$ormDialect, $modelInstance, $identifier
			))->handle(); // when

			$this->assertSame($modelInstance->getEditIntegrity(), $identifier); // then 2
		}
	}
?>