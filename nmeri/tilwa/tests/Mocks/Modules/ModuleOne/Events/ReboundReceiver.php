<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	class ReboundReceiver {

		private $payload;

		public function ricochetReactor ($payload):void {

			$this->payload = $payload;
		}
	}
?>