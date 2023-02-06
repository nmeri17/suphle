<?php
	namespace Suphle\Tests\Integration\Services\Proxies\MultiUserModel;

	use Suphle\Services\DecoratorHandlers\MultiUserEditHandler;

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Contracts\Services\Models\IntegrityModel;

	use Suphle\Exception\Explosives\EditIntegrityException;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Testing\Condiments\{DirectHttpTest, BaseDatabasePopulator};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{EmploymentEditMock, EmploymentEditError};

	use DateTime, DateInterval;

	class MultiEditUpdateTest extends IsolatedComponentTest {

		use DirectHttpTest, BaseDatabasePopulator, CommonBinds {

			BaseDatabasePopulator::setUp as databaseAllSetup;
		}

		private Employment $lastInserted;
		
		private string $modelName = Employment::class,
		
		$sutName = EmploymentEditMock::class;

		protected function setUp ():void { // continue by running tests

			$this->databaseAllSetup();

			$this->lastInserted = $this->replicator->getRandomEntity();
		}

		public function test_missing_key_on_update_throws_error () {

			$this->expectException(EditIntegrityException::class); // then

			$this->setHttpParams("/dummy/5", "put"); // given

			$this->stubPlaceholderStorage(5);

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
			$modelId = $this->lastInserted->id;

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

			$columnName = IntegrityModel::INTEGRITY_COLUMN;

			$modelId = $this->lastInserted->id;

			$this->setJsonParams("/dummy", [

				MultiUserEditHandler::INTEGRITY_KEY => $this->lastInserted->$columnName,

				"name" => "ujunwa",

				"id" => $modelId
			], "put");

			$this->stubPlaceholderStorage($modelId);

			$result = $this->container->getClass(EmploymentEditError::class)

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