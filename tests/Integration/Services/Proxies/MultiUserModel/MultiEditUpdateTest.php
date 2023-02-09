<?php
	namespace Suphle\Tests\Integration\Services\Proxies\MultiUserModel;

	use Suphle\Services\DecoratorHandlers\MultiUserEditHandler;

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Request\PayloadStorage;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Services\Models\IntegrityModel;

	use Suphle\Exception\Explosives\EditIntegrityException;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer, Condiments\BaseDatabasePopulator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{EmploymentEditMock, EmploymentEditError};

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	use DateTime, DateInterval;

	class MultiEditUpdateTest extends ModuleLevelTest {

		use BaseDatabasePopulator {

			BaseDatabasePopulator::setUp as databaseAllSetup;
		}

		private Employment $lastInserted;
		
		private string $modelName = Employment::class,
		
		$sutName = EmploymentEditMock::class;

		protected function setUp ():void { // continue by running tests

			$this->databaseAllSetup();

			$this->lastInserted = $this->replicator->getRandomEntity();
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_missing_key_on_update_throws_error () {

			$this->expectException(EditIntegrityException::class); // then

			$this->stubRequestObjects(5, []);

			$sut = $this->getContainer()->getClass($this->sutName);

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

			$this->stubRequestObjects($modelId, [

				MultiUserEditHandler::INTEGRITY_KEY => $threeMinutesAgo,

				"name" => "ujunwa", "id" => $modelId
			]);

			// when
			$sut = $this->getContainer()->getClass($this->sutName); // to wrap in decorator

			for ($i = 0; $i < 2; $i++) $sut->updateResource(); // first request updates integrityKey. Next iteration should fail
		}

		public function test_update_can_withstand_errors () {

			$columnName = IntegrityModel::INTEGRITY_COLUMN;

			$modelId = $this->lastInserted->id;

			$this->stubRequestObjects($modelId, [

				MultiUserEditHandler::INTEGRITY_KEY => $this->lastInserted->$columnName,

				"name" => "ujunwa",

				"id" => $modelId
			]);

			$result = $this->getContainer()->getClass(EmploymentEditError::class)

			->updateResource(); // when

			$this->assertSame("boo!", $result); // then
		}

		private function stubRequestObjects (int $segmentValue, array $payload):void {

			$payloadStorage = $this->positiveDouble(PayloadStorage::class);

			$payloadStorage->mergePayload($payload);

			$this->massProvide([

				PathPlaceholders::class => $this->positiveDouble(PathPlaceholders::class, [

					"getSegmentValue" => $segmentValue
				]),
				PayloadStorage::class => $payloadStorage
			]);
		}
	}
?>