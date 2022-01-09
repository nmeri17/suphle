<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	class ReboundReceiver extends PayloadReceptor {

		public function ricochetReactor ($payload):void {

			$this->payload = $payload;
		}
	}
?>