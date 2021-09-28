<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	use Tilwa\Events\EventManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Concretes\LocalSender, Events\LocalReceiver};

	class AssignListeners extends EventManager {

		public function registerListeners() {
			
			$this->local(LocalSender::class, LocalReceiver::class)
	        
	        ->on("sample_event", "yHandler")
	        
	        ->on("no_payload", "xHandler");

			/*$this->external(InteractionC::class, ServiceCHandlers::class)
	        ->on(yEvent, "yHandler")
	        ->on(xEvent, "xHandler");*/
		}
	}
?>