<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Tests\Mocks\Interactions\{ModuleOne, ModuleTwo, ModuleThree};

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\{Meta\ModuleTwoDescriptor, Events\ExternalReactor};

	class DecoupledExternalTest extends TestEventManager {

		protected $eventReceiverName = ExternalReactor::class;

		protected function setModuleTwo ():void {

			$this->moduleTwo = $this->replicatorProxy(ModuleTwoDescriptor::class)
			->sendExpatriates([

				ModuleThree::class => $this->moduleThree
			]);
		}

		public function test_can_listen_to_unimported_external () {

			// given => see module injection

			$this->expectUpdatePayload(); // then

			$this->getModuleFor(ModuleOne::class)->payloadEvent($this->payload); // when
		}
	}
?>