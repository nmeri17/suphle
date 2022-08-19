<?php
	namespace Suphle\Tests\Integration\Events;

	use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\LocalSender;

	class BasicEventTest extends EventTestCreator {

		protected function setUp ():void {

			$this->parentSetUp();
		}

		public function test_can_trap_events () {

			$this->getModuleFor(ModuleOne::class)->noPayloadEvent(); // when

			$this->assertHandledEvent(
				LocalSender::class, ModuleOne::EMPTY_PAYLOAD_EVENT
			); // then
		}
	}
?>