<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Events;

	class ExternalReactor {

		private $payload;

		public function updatePayload ($payload):void {

			$this->payload = $payload;
		}
	}
?>