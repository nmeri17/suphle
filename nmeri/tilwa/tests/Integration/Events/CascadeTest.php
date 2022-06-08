<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Events\ReboundReceiver, Meta\ModuleOneDescriptor};

	class CascadeTest extends TestEventManager {

		protected $eventReceiverName = ReboundReceiver::class;

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicatorProxy(ModuleOneDescriptor::class);
		}

		public function test_local_emit_cascades_to_local () {

			// given => see module injection

			$this->mockCalls([

				"ricochetReactor" => [1, [$this->payload]]
			], $this->mockEventReceiver); // then

			$this->getModuleFor(ModuleOne::class)

			->cascadeEntryEvent($this->payload); // when
		}
	}
?>