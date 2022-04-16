<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Testing\Proxies\StubbedQueueAdapter;

	use Tilwa\Flows\{Jobs\RouteBranches, OuterFlowWrapper};

	use Tilwa\Contracts\Queues\Adapter;

	trait QueueInterceptor {

		private $adapter;

		public function setUp ():void {

			parent::setUp();

			$this->catchQueuedTasks();
		}

		private function catchQueuedTasks ():void {

			if (is_null($this->adapter)) { // using this nonce so we can assert more than once in the same test without overwriting the instance

				$this->adapter = new StubbedQueueAdapter;

				$this->massProvide([Adapter::class => $this->adapter]); // since we don't know yet what the active module is at this point this
			}
		}

		protected function assertPushed (string $taskName):void {

			$this->assertTrue(
				$this->adapter->didPushTask($taskName),

				"Failed asserting that $taskName was pushed to queue"
			);
		}

		protected function assertNotPushed (string $taskName):void {

			$this->assertFalse(
				$this->adapter->didPushTask($taskName),

				"Did not expect $taskName to be pushed to queue"
			);
		}

		protected function assertPushedToFlow(string $originUrl):void {

			$this->get($originUrl);

			$this->assertPushed(RouteBranches::class);
		}

		protected function assertNotPushedToFlow(string $originUrl):void {

			$this->get($originUrl);

			$this->assertNotPushed(RouteBranches::class);
		}

		protected function assertHandledByFlow (string $flowUrl):void {

			$this->get($flowUrl); // When

			$this->assertTrue(

				$this->getFlowWrapper()->canHandle(),

				"Failed asserting that request to $flowUrl was handled by Flow"
			); // then
		}

		protected function assertNotHandledByFlow (string $url):void {

			$this->get($url); // When

			$this->assertFalse(

				$this->getFlowWrapper()->canHandle(),

				"Request to $url was not expected to be handled by Flow"
			); // then
		}

		private function getFlowWrapper ():OuterFlowWrapper {

			return $this->firstModuleContainer()->getClass(OuterFlowWrapper::class);
		}
	}
?>