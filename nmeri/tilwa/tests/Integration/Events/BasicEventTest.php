<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\LocalSender;

	class BasicEventTest extends TestEventManager {

		public function test_can_trap_events () {

			$this->getModuleFor(ModuleOne::class)->noPayloadEvent(); // when

			$this->assertFiredEvent(
				LocalSender::class, ModuleOne::EMPTY_PAYLOAD_EVENT
			); // then
		}
	}
?>