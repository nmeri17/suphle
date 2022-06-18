<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Events\ReboundReceiver, Meta\ModuleOneDescriptor};

	class CascadeTest extends EventTestCreator {

		protected $eventReceiverName = ReboundReceiver::class;

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicatorProxy(ModuleOneDescriptor::class);
		}

		public function test_local_emit_cascades_to_local () {

			$this->setMockEventReceiver([

				"ricochetReactor" => [1, [$this->payload]]
			], []); // then

			$this->parentSetUp(); // given

			$this->getModuleFor(ModuleOne::class)

			->cascadeEntryEvent($this->payload); // when
		}
	}
?>