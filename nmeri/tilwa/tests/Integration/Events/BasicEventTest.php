<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class BasicEventTest extends TestEventManager {

		public function test_can_trap_events() {

			$this->assertFiredEvent(
				$this->getModuleFor(ModuleOne::class)

				->noPayloadEvent(), ModuleOne::EMPTY_PAYLOAD_EVENT
			);
		}
	}
?>