<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\LocalReceiver};

	class LocalPayloadTest extends TestEventManager {

		protected $eventReceiverName = LocalReceiver::class;

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicatorProxy(ModuleOneDescriptor::class);
		}

		public function test_can_receive_emitted_payload () {

			// given => see module injection

			$this->mockEventReceiver->updatePayload($this->payload)->shouldBeCalled(); // then

			$this->getModuleFor(ModuleOne::class)->payloadEvent($this->payload); // when
		}
	}
?>