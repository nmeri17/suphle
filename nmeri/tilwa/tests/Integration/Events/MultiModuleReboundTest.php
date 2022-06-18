<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\{Meta\ModuleThreeDescriptor, Events\ReboundReceiver};

	class MultiModuleReboundTest extends EventTestCreator {

		protected $eventReceiverName = ReboundReceiver::class;

		protected function setModuleThree ():void {

			$this->moduleThree = $this->replicatorProxy(ModuleThreeDescriptor::class)
			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_local_emit_cascades_to_multiple_external () {

			$this->setMockEventReceiver([

				"handleMultiModuleRebound" => [1, []]
			], []); // then

			$this->parentSetUp(); // given

			$this->getModuleFor(ModuleOne::class)->multiModuleCascadeEvent(true); // when
		}
	}
?>