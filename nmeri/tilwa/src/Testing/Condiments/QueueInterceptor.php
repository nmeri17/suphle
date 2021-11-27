<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Testing\Proxies\{FrontDoorTest, Extensions\AdapterTester};

	trait QueueInterceptor {

		use FrontDoorTest; // this should be the same instance used to send the request

		public function catchQueuedTasks ():void {

			/*this guy should extend the adapter manager*/ new AdapterTester // this should be in the active container instead
		}

		protected function assertPushed (string $taskName):void {

			// interact with the queueFaker
		}
	}
?>