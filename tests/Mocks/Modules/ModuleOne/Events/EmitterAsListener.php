<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Events;

	use Suphle\Events\EventManager;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Events\LocalReceiver;

	class EmitterAsListener extends EventManager {

		public function registerListeners ():void {
			
			$this->local(LocalReceiver::class, LocalReceiver::class)
			
			->on(LocalReceiver::CASCADE_REBOUND_EVENT, "updatePayload");
		}
	}
?>