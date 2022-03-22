<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	use Tilwa\Events\EventManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Events\LocalReceiver;

	class EmitterAsListener extends EventManager {

		public function registerListeners ():void {
			
			$this->local(LocalReceiver::class, LocalReceiver::class)
	        
	        ->on(LocalReceiver::CASCADE_REBOUND_EVENT, "updatePayload");
		}
	}
?>