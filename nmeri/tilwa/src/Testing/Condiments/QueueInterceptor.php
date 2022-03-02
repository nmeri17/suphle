<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Testing\Proxies\StubbedQueueAdapter;

	use Tilwa\Flows\{Jobs\RouteBranches, OuterFlowWrapper};

	use Tilwa\Contracts\Queues\Adapter;

	trait QueueInterceptor {

		private $adapter;

		public function setUp () {

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

			$this->assertTrue($this->adapter->didPushTask($taskName));
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

			$this->assertTrue($this->getFlowWrapper()->canHandle()); // then
		}

		protected function assertNotHandledByFlow (string $url):void {

			$this->get($url); // When

			$this->assertFalse($this->getFlowWrapper()->canHandle()); // then
		}

		private function getFlowWrapper ():OuterFlowWrapper {

			return $this->firstModuleContainer()->getClass(OuterFlowWrapper::class);
		}
	}
?>