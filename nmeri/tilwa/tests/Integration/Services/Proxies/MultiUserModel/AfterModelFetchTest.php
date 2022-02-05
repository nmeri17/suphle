<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\MultiUserModel;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\QueueInterceptor};

	use Tilwa\Services\Jobs\AddUserEditField;

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Concretes\Services\MultiUserEditMock, Meta\ModuleOneDescriptor};

	class AfterModelFetchTest extends ModuleLevelTest {

		use QueueInterceptor {

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
	}
?>