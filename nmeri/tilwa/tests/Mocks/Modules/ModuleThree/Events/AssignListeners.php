<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Events;

	use Tilwa\Events\EventManager;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class AssignListeners extends EventManager {

		public function registerListeners() {
			
			$this->external(ModuleOne::class, EventsHandler::class)
	        
	        ->on(ModuleOne::DEFAULT_EVENT, "setExternalPayload")
	        
	        /*->on(xEvent, "xHandler")*/;
		}
	}
?>