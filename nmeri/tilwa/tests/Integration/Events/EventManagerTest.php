<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\EmittedEventsCatcher};

	use Tilwa\Tests\Mocks\Interactions\{ ModuleOne, ModuleTwo, ModuleThree};

	use Tilwa\Tests\Integration\App\ModuleDescriptor\DescriptorCollection;

	class EventManagerTest extends DescriptorCollection {

		use EmittedEventsCatcher {

			MockModuleEvents::setUp as eventsSetup;
		};

		private $payload = 5;

		public function setUp () {

			$this->setModuleOne();

			$this->setModuleThree();

			$this->setModuleTwo();

			$this->eventsSetup();
		}
		
		protected function getModules ():array {

			return [ $this->moduleOne, $this->moduleThree];
		}

		public function test_can_trap_events() {

			$this->assertFiredEvent(
				$this->getModuleFor(ModuleOne::class)

				->noPayloadEvent(), ModuleOne::EMPTY_PAYLOAD_EVENT
			);
		}

		public function test_can_receive_emitted_payload () {

			$module = $this->getModuleFor(ModuleOne::class);

			$module->payloadEvent($this->payload); // when
			
			$this->assertSame($module->getLocalReceivedPayload(), $this->payload); // then
		}

		public function test_can_listen_to_imported_external () {

			$module1 = $this->getModuleFor(ModuleOne::class);

			$module1->payloadEvent($this->payload); // when

			$module3 = $this->getModuleFor(ModuleThree::class);
			
			$this->assertSame($module3->coupledExternalReceivedPayload(), $this->payload); // then
		}

		public function test_can_listen_to_unimported_external () {

			$module1 = $this->getModuleFor(ModuleOne::class);

			$module1->payloadEvent($this->payload); // when

			$module2 = $this->getModuleFor(ModuleTwo::class);
			
			$this->assertSame($module2->decoupledExternalReceivedPayload(), $this->payload); // then
		}

		public function test_local_emit_cascades_to_local () {

			$module1 = $this->getModuleFor(ModuleOne::class);

			$module1->cascadeEntryEvent($this->payload); // when

			$this->assertSame($module1->cascadeFinalPayload(), $this->payload); // then 
		}

		public function test_space_delimited_event_names () {

			$module = $this->getModuleFor(ModuleOne::class);

			$this->assertFiredEvent($module->noPayloadEvent(), ModuleOne::EMPTY_PAYLOAD_EVENT); // continue here

			// that's, multiple events to one handler
		}

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