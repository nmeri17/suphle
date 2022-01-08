<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	use Tilwa\Events\EventManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Concretes\LocalSender as Emitter, Events\LocalReceiver};

	class AssignListeners extends EventManager {

		public function registerListeners() {
			
			$this->local(Emitter::class, LocalReceiver::class)
	        
	        ->on(Emitter::DEFAULT_EVENT, "updatePayload")
	        
	        ->on(Emitter::EMPTY_PAYLOAD_EVENT, "setDefaultPayload");
		}
	}
?>