<?php
	namespace Tilwa\Tests\Integration\Events;

	use Tilwa\Testing\ModuleLevelTest;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne\ModuleOneDescriptor;

	class EventManagerTest extends ModuleLevelTest { // work with [registerListeners]
		
		protected function getModules():array {

			return [ModuleOneDescriptor::class];
		}

		public function test_can_trap_events() {

			$this->catchEmittingEvents();

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

		public function test_can_emit_to_external () {
			
			//
		}

		public function test_local_emit_cascades_to_local() {
			
			//
		}

		public function test_local_emit_cascades_to_external () {
			
			//
		}
	}
?>