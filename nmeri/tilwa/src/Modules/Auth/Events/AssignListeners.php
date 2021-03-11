<?php
	namespace Tilwa\Modules\Auth\Events;

	use Tilwa\Events\EventManager;

	class AssignListeners extends EventManager {

		public function registerListeners() {
			
			$this->local(InterfaceB::class, ServiceBHandlers::class)
	        ->on(yEvent, "yHandler")
	        ->on(xEvent, "xHandler");

			$this->external(InteractionC::class, ServiceCHandlers::class)
	        ->on(yEvent, "yHandler")
	        ->on(xEvent, "xHandler");
		}
	}
?>