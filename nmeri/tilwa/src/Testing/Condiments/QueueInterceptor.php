<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Testing\Proxies\{FrontDoorTest, Extensions\AdapterTester};

	use Tilwa\Flows\{Jobs\RouteBranches, OuterFlowWrapper};

	trait QueueInterceptor {

		use FrontDoorTest; // this should be the same instance used to send the request

		public function catchQueuedTasks ():void {

			/*this guy should extend the adapter manager*/ new AdapterTester // this should be in the active container instead
		}

		protected function assertPushed (string $taskName):void {

			// interact with the queueFaker
		}

		protected function assertPushedToFlow(string $originUrl):void {

			$this->catchQueuedTasks();

			$this->get($originUrl);

			$this->assertPushed(RouteBranches::class);
		}

		protected function assertNotPushedToFlow(string $originUrl):void {

			$this->catchQueuedTasks();

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