<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Proxies\Extensions\MockModuleEvents};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne\ModuleOneDescriptor;

	class EventManagerTest extends ModuleLevelTest {

		use MockModuleEvents {

			MockModuleEvents::setUp as eventsSetup;
		};

		public function setUp () {

			$this->eventsSetup();
		}
		
		protected function getModules():array {

			return [ new ModuleOneDescriptor(new Container)];
		}

		public function test_can_trap_events() {

			$module = $this->getModuleFor(ModuleOne::class);

			$sender = $module->getLocalSender();

			$sender->sendLocalEventNoPayload();
			
			$this->assertFiredEvent($sender, $sender->getEventName());
		}

		public function test_can_emit_and_fire_local() {

			$module = $this->getModuleFor(ModuleOne::class);

			$payload = 5;

			$sender = $module->getLocalSender();

			$receiver = $module->getLocalReceiver();

			$sender->sendLocalEvent($payload);
			
			$this->assertSame($receiver->getPayload(), $payload);
		}

		public function test_can_listen_to_external () {
			
			//
		}

		public function test_local_emit_cascades_to_local() {
			
			//
		}

		public function test_local_emit_cascades_to_external () {
			
			//
		}

		public function test_space_delimited_event_names () {

			//
		}

		public function test_Local_listeners_are_decoupled_from_their_emittor () {

			//
		}

		public function test_Repository_handler_gets_wrapped () {

			//
		}
	}
?>