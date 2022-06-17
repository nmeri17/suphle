<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\{Meta\ModuleThreeDescriptor, Events\EventsHandler};

	class CoupledExternalTest extends EventTestCreator {

		protected $eventReceiverName = EventsHandler::class,

		$mockReceiverMethods = [

			"setExternalPayload", "handleImpossibleEmit"
		];

		protected function setModuleThree ():void {

			$this->moduleThree = $this->replicatorProxy(ModuleThreeDescriptor::class)
			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_can_listen_to_imported_external () {

			// given => see module injection

			$this->mockCalls([

				"setExternalPayload" => [1, [$this->payload]]
			], $this->mockEventReceiver); // then

			$this->getModuleFor(ModuleOne::class)->payloadEvent($this->payload); // when
		}

		public function test_local_bind_cant_react_to_external_emission () {

			// given => see module injection

			$this->mockCalls([

				"handleImpossibleEmit" => [0, [$this->payload]]
			], $this->mockEventReceiver); // then

			$this->getModuleFor(ModuleOne::class)->payloadEvent($this->payload); // when
		}
	}
?>