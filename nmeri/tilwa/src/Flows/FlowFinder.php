<?php
	namespace Tilwa\Flows;

	use Tilwa\Http\Response\BaseResponseManager;

	use Tilwa\Events\EventManager;

	class FlowFinder extends BaseResponseManager {

		private $eventManager; // this is deduced from the modules loader when the flow is hydrated from our cache

		private function matchesRequest():bool {
			
			//
		}

		private function flowExists():bool {
			
			//
		}

		public function getResponse():string {

			$context = $this->getActiveFlow();

			$renderer = $context->hydrateRenderer();

			$controllerManager = $this->getControllerManager($renderer);

			$renderer->invokeActionHandler($controllerManager->getHandlerParameters());

			return $renderer->render();
		}

		public function setLastResponseNodes($nextResponse):void { // should include the full renderer as well and not just the response
			
			//
		}

		private function getActiveFlow() {
			
			// find the flow request in the cache matching current get parameters
		}

		// contents of this method should be queued
		public function flush() {

			$this->setLastResponseNodes($body);

			// review this parameter
			$this->rendererModule->eventManager->emit($renderer->getController(), "on_flow_hit", $body); // should probably include request parameters, too?
		}

		public function shouldRespond():bool {
			
			return $this->matchesRequest() && $this->flowExists();
		}
	}
?>