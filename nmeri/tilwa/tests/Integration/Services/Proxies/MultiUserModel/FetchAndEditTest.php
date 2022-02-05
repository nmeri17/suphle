<?php
	namespace Tilwa\Tests\Integration\Services\Proxies;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Testing\Condiments\{QueueInterceptor, MockFacilitator};

	use Tilwa\Services\Jobs\AddUserEditField;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\MultiUserEditMock;

	class MultiUserModelEditTest extends ModuleLevelTest {

		use MockFacilitator, QueueInterceptor {

			QueueInterceptor::setUp as queueSetup;
		};

		public function setUp ():void {

			parent::setUp();

			$this->queueSetup();
		}
		
		protected function getModules():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_get_resource_sets_integrity_on_service () {

			$sut = $this->container->getClass(MultiUserEditMock::class); // given

			$sut->getResource(); // when

			// then
			$this->assertNotNull($sut->getLastIntegrity());

			$this->assertPushed(AddUserEditField::class);
		}

		public function test_get_queue_adds_integrity () {

			$sut = $this->positiveStub(AddUserEditField::class, [], [ // using a stub to avoid injecting a live ormDialect

				"modelInstance" => $this->positiveStub(IntegrityModel::class)->expects($this->once())

					->method("addEditIntegrity")->with($this->greaterThan(20)) // then
			]); // given

			$sut->handle(); // when
		}

		public function test_missing_key_on_update_throws_error () {

			$this->setExpectedException(EditIntegrityException::class); // then

			//
		}

		public function test_last_updater_invalidates_for_all_viewers () {

			$this->setExpectedException(EditIntegrityException::class); // then

			// 
		}

		public function test_last_updater_successfully_updates () {

			// ...and nullifies
		}

		public function test_update_can_withstand_errors () {

			// sut => AddUserEditField
		}
	}
?>