<?php
	namespace Suphle\Tests\Integration\Services\Proxies\SystemModelEdit;

	use Suphle\Hydration\Container;

	use Suphle\Testing\TestTypes\ModuleLevelTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	class UpdateEventErrorTest extends ModuleLevelTest {
		
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