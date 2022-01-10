<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Tests\Integration\App\ModuleDescriptor\DescriptorCollection;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	class TestEventManager extends DescriptorCollection {

		use EmittedEventsCatcher {

			EmittedEventsCatcher::setUp as eventsSetup;
		};

		protected $payload = 5, $mockEventReceiver,

		$eventReceiverName;

		public function setUp () {

			if (!is_null($this->eventReceiverName))

				$this->mockEventReceiver = $this->getProphet()->prophesize($this->eventReceiverName);

			$this->setModuleOne();

			$this->setModuleThree();

			$this->setModuleTwo();

			$this->eventsSetup();
		}
		
		protected function getModules ():array {

			return [
				$this->moduleOne, $this->moduleTwo,

				$this->moduleThree
			];
		}

		/**
		 * The receiver, [eventReceiverName], will be replaced in the listening module with a mock allowing us know whether it actually handled event
		 * 
		 * @param {descriptorName}: The module receiving the event to be emitted
		 * 
		 * @return new module with updates
		*/
		protected function replicatorProxy (string $descriptorName):ModuleDescriptor {

			return $this->replicateModule($descriptorName, function(WriteOnlyContainer $container) {

				$container->replaceWithConcrete($this->eventReceiverName, $this->mockEventReceiver->reveal());
			});
		}

// move these to their own test classes
		public function test_cant_listen_on_emitter () {

			// then
		}

		public function test_local_bind_cant_react_to_external_emission () {

			//
		}

		public function test_local_emit_cascades_to_external () {
			
			// 2nd => external, 1st => external, rebound to external
		}

		public function test_listeners_can_listen_to_subclass_emittor () {

			// we listen on the parent, then a child emits
		}
	}
?>