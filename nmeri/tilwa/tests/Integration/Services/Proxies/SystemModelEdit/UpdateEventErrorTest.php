<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\SystemModelEdit;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\EmittedEventsCatcher};

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class UpdateEventErrorTest extends ModuleLevelTest {

		use EmittedEventsCatcher;
		
		protected function getModules():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_error_in_event_handler_terminates_transaction () {

			$payload = 15; // given

			$result = $this->getModuleFor(ModuleOne::class)

			->systemUpdateErrorEvent($payload); // when

			$this->assertEquals($result, $payload); // then
		}
	}
?>