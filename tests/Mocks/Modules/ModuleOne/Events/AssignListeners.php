<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Events;

	use Suphle\Events\EventManager;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{LocalSender as Emitter, Services\UpdatefulEmitter};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Events\{LocalReceiver, UpdatefulListener};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleApi;

	class AssignListeners extends EventManager {

		public function registerListeners():void {

			parent::registerListeners();
			
			$this->localSenderBindings();

			$this->localReceiverBindings();

			$this->updatefulBindings();
		}

		private function localSenderBindings ():void {

			$this->local(Emitter::class, LocalReceiver::class)

			->on(ModuleApi::DEFAULT_EVENT, "updatePayload")

			->on(Emitter::EMPTY_PAYLOAD_EVENT, "doNothing")

			->on(Emitter::CASCADE_BEGIN_EVENT, "reboundsNewEvent")

			->on(Emitter::EMPTY_PAYLOAD_EVENT . " " . Emitter::CONCAT_EVENT, "unionHandler")

			->on(Emitter::CASCADE_EXTERNAL_BEGIN_EVENT, "reboundExternalEvent");
		}

		private function localReceiverBindings ():void {
			
			$this->local(LocalReceiver::class, ReboundReceiver::class)

			->on(LocalReceiver::CASCADE_REBOUND_EVENT, "ricochetReactor");
	    }

	    private function updatefulBindings ():void {

	    	$this->local(UpdatefulEmitter::class, UpdatefulListener::class)

	    	->on(UpdatefulEmitter::UPDATE_ERROR, "terminateTransaction");
	    }
	}
?>