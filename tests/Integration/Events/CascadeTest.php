<?php
	namespace Suphle\Tests\Integration\Events;

	use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Events\ReboundReceiver, Meta\ModuleOneDescriptor};

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