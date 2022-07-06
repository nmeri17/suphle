<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\MultiUserModel;

	use Tilwa\Services\DecoratorHandlers\MultiUserEditHandler;

	use Tilwa\Routing\PathPlaceholders;

	use Tilwa\Contracts\Services\Models\IntegrityModel;

	use Tilwa\Exception\Explosives\EditIntegrityException;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\{DirectHttpTest, BaseDatabasePopulator};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Models\Eloquent\MultiEditProduct;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{MultiUserEditMock, MultiUserEditError};

	use DateTime, DateInterval;

	class MultiEditUpdateTest extends IsolatedComponentTest {

		use DirectHttpTest, BaseDatabasePopulator, CommonBinds;

		private $modelName = MultiEditProduct::class,

		$sutName = MultiUserEditMock::class;

		protected $usesRealDecorator = true;

		public function test_missing_key_on_update_throws_error () {

			$this->expectException(EditIntegrityException::class); // then

			$this->setHttpParams("/dummy", "put"); // given

			$sut = $this->container->getClass($this->sutName);

			$sut->updateResource(); // when
		}

		protected function getActiveEntity ():string {

			return $this->modelName;
		}

		public function test_last_updater_invalidates_for_all_viewers () {

			$this->expectException(EditIntegrityException::class); // then

			$threeMinutesAgo = (new DateTime)->sub(new DateInterval("PT3M"))

			->format(MultiUserEditHandler::DATE_FORMAT);

			// given
			$modelId = $this->replicator->getRandomEntity()->id;

			$this->setJsonParams("/dummy", [

				MultiUserEditHandler::INTEGRITY_KEY => $threeMinutesAgo,

				"name" => "ujunwa", "id" => $modelId
			], "put");

			$this->stubPlaceholderStorage($modelId);

			// when
			$sut = $this->container->getClass($this->sutName); // to wrap in decorator

			for ($i = 0; $i < 2; $i++) $sut->updateResource(); // first request updates integrityKey. Next iteration should fail
		}

		public function test_update_can_withstand_errors () {

			$model = $this->replicator->getRandomEntity();

			$columnName = IntegrityModel::INTEGRITY_COLUMN;

			$this->setJsonParams("/dummy", [

				MultiUserEditHandler::INTEGRITY_KEY => $model->$columnName,

				"name" => "ujunwa",

				"id" => $model->id
			], "put");

			$this->stubPlaceholderStorage($model->id);

			$result = $this->container->getClass(MultiUserEditError::class)

			->updateResource(); // when

			$this->assertSame("boo!", $result); // then
		}

		private function stubPlaceholderStorage (int $segmentValue):void {

			$storageName = PathPlaceholders::class;

			$this->massProvide([

				$storageName => $this->positiveDouble($storageName, [

					"getSegmentValue" => $segmentValue
				])
			]);
		}
	}
?>