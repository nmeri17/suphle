<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Events;

	use Suphle\Events\EventManager;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	class AssignListeners extends EventManager {

		public function registerListeners ():void {
			
			$this->moduleOneBindings();

			$this->incompatibleBindings();

			$this->localReceiverBindings();
		}

		private function moduleOneBindings ():void {
			
			$this->external(ModuleOne::class, EventsHandler::class)
			
			->on(ModuleOne::DEFAULT_EVENT, "setExternalPayload")
			
			->on(ModuleOne::OUTSIDERS_REBOUND_EVENT, "handleExternalRebound");
		}

		private function incompatibleBindings ():void {
			
			$this->local(ModuleOne::class, EventsHandler::class)
			
			->on(ModuleOne::DEFAULT_EVENT, "handleImpossibleEmit");
		}

		private function localReceiverBindings ():void {

			$this->local(EventsHandler::class, ReboundReceiver::class)
			
			->on(EventsHandler::EXTERNAL_LOCAL_REBOUND, "handleMultiModuleRebound");
		}
	}
?>