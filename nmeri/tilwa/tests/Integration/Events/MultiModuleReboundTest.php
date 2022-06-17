<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\{Meta\ModuleThreeDescriptor, Events\ReboundReceiver};

	class MultiModuleReboundTest extends EventTestCreator {

		protected $eventReceiverName = ReboundReceiver::class,

		$mockReceiverMethods = ["handleMultiModuleRebound"];

		protected function setModuleThree ():void {

			$this->moduleThree = $this->replicatorProxy(ModuleThreeDescriptor::class)
			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_local_emit_cascades_to_multiple_external () {

			// given => see module injection

			$this->mockCalls([

				"handleMultiModuleRebound" => [1, []]
			], $this->mockEventReceiver); // then

			$this->getModuleFor(ModuleOne::class)->multiModuleCascadeEvent(true); // when
		}
	}
?>