<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\{Meta\ModuleThreeDescriptor, Events\EventsHandler};

	class CascadeExternalTest extends EventTestCreator {

		protected $eventReceiverName = EventsHandler::class;

		protected function setModuleThree ():void {

			$this->moduleThree = $this->replicatorProxy(ModuleThreeDescriptor::class)
			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_local_emit_cascades_to_external () {

			$this->setMockEventReceiver([ // then

				"handleExternalRebound" => [1, [false]]
			]);

			$this->parentSetUp(); // given

			$this->getModuleFor(ModuleOne::class)->multiModuleCascadeEvent(false); // when
		}
	}
?>