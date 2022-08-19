<?php
	namespace Suphle\Testing\Condiments;

	use Suphle\Testing\Proxies\StubbedQueueAdapter;

	use Suphle\Flows\{Jobs\RouteBranches, OuterFlowWrapper};

	use Suphle\Contracts\Queues\Adapter;

	trait QueueInterceptor {

		private $queueAdapter;

		public function setUp ():void {

			parent::setUp();

			$this->catchQueuedTasks();
		}

		protected function catchQueuedTasks ():void {

			if (is_null($this->queueAdapter)) // using this nonce so we can assert more than once in the same test without overwriting the instance

				$this->massProvide([

					Adapter::class => $this->queueAdapter = new StubbedQueueAdapter // mass providing from the onset since we don't know yet what the active module is at this point this
				]);
		}

		protected function assertPushed (string $taskName):void {

			$this->assertTrue(
				$this->queueAdapter->didPushTask($taskName),

				"Failed asserting that $taskName was pushed to queue"
			);
		}

		protected function assertNotPushed (string $taskName):void {

			$this->assertFalse(
				$this->queueAdapter->didPushTask($taskName),

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

			// the [given] part is in whatever action was taken before calling this method
			$this->setHttpParams($flowUrl); // When

			$this->assertTrue(

				$this->getFlowWrapper()->canHandle(),

				"Failed asserting that request to '$flowUrl' was handled by Flow"
			); // then
		}

		protected function assertNotHandledByFlow (string $url):void {

			$this->setHttpParams($url); // When

			$this->assertFalse(

				$this->getFlowWrapper()->canHandle(),

				"Request to $url was not expected to be handled by Flow"
			); // then
		}

		private function getFlowWrapper ():OuterFlowWrapper {

			return $this->firstModuleContainer()->getClass(OuterFlowWrapper::class);
		}

		protected function processQueuedTasks ():void {

			$this->getContainer()->getClass(Adapter::class)

			->processTasks();
		}
	}
?>