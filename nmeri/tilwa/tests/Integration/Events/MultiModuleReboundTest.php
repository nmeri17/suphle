<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\{Meta\ModuleThreeDescriptor, Events\ReboundReceiver};

	class MultiModuleReboundTest extends TestEventManager {

		protected $eventReceiverName = ReboundReceiver::class;

		protected function setModuleThree ():void {

			$this->moduleThree = $this->replicatorProxy(ModuleThreeDescriptor::class)
			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_local_emit_cascades_to_multiple_external () {

			// given => see module injection

			$this->mockEventReceiver->handleMultiModuleRebound()->shouldBeCalled(); // then

			$this->getModuleFor(ModuleOne::class)->multiModuleCascadeEvent(true); // when
		}
	}
?>