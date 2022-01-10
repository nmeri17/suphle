<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Events;

	use Tilwa\Events\EventManager;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class AssignListeners extends EventManager {

		public function registerListeners() {
			
			$this->moduleOneBindings();

			$this->incompatibleBindings();
		}

	    private function moduleOneBindings ():void {
			
			$this->external(ModuleOne::class, EventsHandler::class)
	        
	        ->on(ModuleOne::DEFAULT_EVENT, "setExternalPayload");
	    }

	    private function incompatibleBindings ():void {
			
			$this->local(ModuleOne::class, EventsHandler::class)
	        
	        ->on(ModuleOne::DEFAULT_EVENT, "reactToExternalEmit");
	    }
	}
?>