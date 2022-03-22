<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Events;

	use Tilwa\Events\EventManager;

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Events\ExternalReactor;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class AssignListeners extends EventManager {

		public function registerListeners():void {
			
			$this->external(ModuleOne::class, ExternalReactor::class)
	        
	        ->on(ModuleOne::DEFAULT_EVENT, "updatePayload");
		}
	}
?>