<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\{Meta\ModuleThreeDescriptor, Events\EventsHandler};

	class CoupledExternalTest extends TestEventManager {

		protected $eventReceiverName = EventsHandler::class;

		protected function setModuleThree ():void {

			$this->moduleThree = $this->replicatorProxy(ModuleThreeDescriptor::class)
			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_can_listen_to_imported_external () {

			// given => see module injection

			$this->mockEventReceiver->setExternalPayload($this->payload)->shouldBeCalled(); // then

			$this->getModuleFor(ModuleOne::class)->payloadEvent($this->payload); // when
		}
	}
?>