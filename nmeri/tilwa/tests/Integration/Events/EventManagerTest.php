<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\EmittedEventsCatcher};

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

	use Tilwa\Tests\Integration\App\ModuleDescriptor\DescriptorCollection;

	class EventManagerTest extends DescriptorCollection {

		use EmittedEventsCatcher {

			MockModuleEvents::setUp as eventsSetup;
		};

		private $payload = 5;

		public function setUp () {

			$this->setModuleOne();

			$this->setModuleThree();

			$this->eventsSetup();
		}
		
		protected function getModules ():array {

			return [ $this->moduleOne, $this->moduleThree];
		}

		public function test_can_trap_events() {

			$module = $this->getModuleFor(ModuleOne::class);

			$this->assertFiredEvent($module->noPayloadEvent(), $module->emittedEventName());
		}

		public function test_can_receive_emitted_payload () {

			$module = $this->getModuleFor(ModuleOne::class);

			$module->payloadEvent($this->payload);
			
			$this->assertSame($module->getLocalReceivedPayload(), $this->payload);
		}

		public function test_can_listen_to_external () {

			$module1 = $this->getModuleFor(ModuleOne::class);

			$module1->payloadEvent($this->payload);

			$module3 = $this->getModuleFor(ModuleThree::class);
			
			$this->assertSame($module3->getExternalReceivedPayload(), $this->payload);
		}

		public function test_local_emit_cascades_to_local() {
			
			// when a listener equally emits
		}

		public function test_local_emit_cascades_to_external () {
			
			//
		}

		public function test_space_delimited_event_names () {

			// that's, multiple events to one handler
		}
	}
?>