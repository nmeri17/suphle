<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	use Exception;

	class UpdatefulListener {

		public function terminateTransaction ($payload):void {

			throw new Exception;
		}
	}
?>