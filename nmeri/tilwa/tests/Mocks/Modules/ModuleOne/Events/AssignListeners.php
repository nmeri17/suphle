<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	use Tilwa\Events\EventManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Concretes\LocalSender as Emitter, Events\LocalReceiver};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleApi;

	class AssignListeners extends EventManager {

		public function registerListeners():void {
			
			$this->localSenderBindings();

			$this->localReceiverBindings();
		}

		private function localSenderBindings ():void {

			$this->local(Emitter::class, LocalReceiver::class)
	        
	        ->on(ModuleApi::DEFAULT_EVENT, "updatePayload")
	        
	        ->on(Emitter::EMPTY_PAYLOAD_EVENT, "doNothing")
	        
	        ->on(Emitter::CASCADE_BEGIN_EVENT, "reboundsNewEvent")
	        
	        ->on(Emitter::EMPTY_PAYLOAD_EVENT . " " . Emitter::CONCAT_EVENT, "unionHandler");
		}

		private function localReceiverBindings ():void {
			
			$this->local(LocalReceiver::class, ReboundReceiver::class)
	        
	        ->on(LocalReceiver::CASCADE_REBOUND_EVENT, "ricochetReactor");
	    }
	}
?>