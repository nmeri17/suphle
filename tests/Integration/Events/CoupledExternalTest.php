<?php
	namespace Suphle\Tests\Integration\Events;

	use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Suphle\Tests\Mocks\Modules\ModuleThree\{Meta\ModuleThreeDescriptor, Events\EventsHandler};

	class CoupledExternalTest extends EventTestCreator {

		protected $eventReceiverName = EventsHandler::class;

		protected function setModuleThree ():void {

			$this->moduleThree = $this->replicatorProxy(ModuleThreeDescriptor::class)
			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_can_listen_to_imported_external () {

			$this->setMockEventReceiver([

				"setExternalPayload" => [1, [$this->payload]]
			]); // then

			$this->parentSetUp(); // given

			$this->getModuleFor(ModuleOne::class)->payloadEvent($this->payload); // when
		}

		public function test_local_bind_cant_react_to_external_emission () {

			$this->setMockEventReceiver([

				"handleImpossibleEmit" => [0, [$this->payload]]
			]); // then

			$this->parentSetUp(); // given

			$this->getModuleFor(ModuleOne::class)->payloadEvent($this->payload); // when
		}
	}
?>