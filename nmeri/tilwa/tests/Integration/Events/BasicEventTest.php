<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\LocalSender;

	class BasicEventTest extends EventTestCreator {

		public function test_can_trap_events () {

			$this->getModuleFor(ModuleOne::class)->noPayloadEvent(); // when

			$this->assertHandledEvent(
				LocalSender::class, ModuleOne::EMPTY_PAYLOAD_EVENT
			); // then
		}
	}
?>