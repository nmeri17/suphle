<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\TestLocalReceiver;

	class LocalPayloadTest extends TestLocalReceiver {

		public function test_can_receive_emitted_payload () {

			// given => see module injection

			$this->expectUpdatePayload(); // then

			$this->getModuleOne()->payloadEvent($this->payload); // when
		}

		// we listen on the parent, then a child emits
		public function test_listeners_can_listen_to_subclass_emittor () {

			// given => see module injection

			$this->expectUpdatePayload(); // then

			$this->getModuleOne()->sendExtendedEvent($this->payload); // when
		}
	}
?>