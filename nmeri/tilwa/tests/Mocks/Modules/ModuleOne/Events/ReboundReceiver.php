<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	class ReboundReceiver {

		public function ricochetReactor ($payload):void {

			$this->payload = $payload;
		}
	}
?>