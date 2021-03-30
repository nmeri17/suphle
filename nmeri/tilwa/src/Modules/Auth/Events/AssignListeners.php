<?php
	namespace Tilwa\Modules\Auth\Events;

	use Tilwa\Events\EventManager;

	class AssignListeners extends EventManager {

		public function registerListeners() {
			
			$this->local(ControllerB::class, ServiceBHandlers::class)
	        ->on("on_hit", "yHandler")
	        ->on(xEvent, "xHandler");

			$this->external(InteractionC::class, ServiceCHandlers::class)
	        ->on(yEvent, "yHandler")
	        ->on(xEvent, "xHandler");
		}
	}
?>