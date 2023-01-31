<?php
	namespace Suphle\Tests\Integration\Orms;

	use Suphle\Contracts\Database\EntityDetails;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\BaseDatabasePopulator};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class EntityDetailsTest extends IsolatedComponentTest {

		use BaseDatabasePopulator, CommonBinds;

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		public function test_correctly_normalizes_identifier () {

			$this->dataProvider([

				$this->modelPrefixDataset(...) // given
			], function (object $model, ?string $prefix, string $expected) {

				$sut = $this->container->getClass(EntityDetails::class);

				// when
				if (!is_null($prefix))

					$result = $sut->idFromModel($model, $prefix);

				else $result = $sut->idFromModel($model);

				$this->assertSame($result, $expected); // then
			});
		}

		public function modelPrefixDataset ():array {

			$model = $this->replicator->getRandomEntity();

			$modelId = $model->id;

			return [

				[$model, null, "employment_$modelId" ],

				[$model, "prefix", "prefix_employment_$modelId" ]
			];
		}
	}
?>