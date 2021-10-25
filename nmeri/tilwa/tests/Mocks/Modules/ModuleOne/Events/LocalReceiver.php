<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	class LocalReceiver {

		private $payload;

		public function getPayload () {

			return $this->payload;
		}

		public function updatePayload ($payload):void {

			$this->payload = $payload;
		}

		public function setDefaultPayload ():void {

			$this->payload = 10;
		}
	}
?>