<?php
	namespace Tilwa\Flows;

	use Tilwa\Contracts\ResponseManager as ManagerInterface;

	use Tilwa\Events\EventManager;

	class FlowFinder implements ManagerInterface {

		private $eventManager; // this is deduced from the modules loader when the flow is hydrated from our cache

		private function matchesRequest():bool {
			
			//
		}

		private function flowExists():bool {
			
			//
		}

		public function getResponse(string $incomingPattern):string {

			$context = $this->getActiveFlow($incomingPattern);

			$renderer = $context->hydrateRenderer();

			// then set the [rawResponse] to the fetched payload

			return $renderer->render();
		}

		private function getActiveFlow(string $incomingPattern) {
			
			// find the flow request in the cache matching current get parameters
		}

		public function afterRender() {

			$this->rendererModule->eventManager->emit($renderer->getController(), "on_flow_hit", $body); // should probably include request parameters, too?
		}

		public function shouldRespond():bool {
			
			return $this->matchesRequest() && $this->flowExists();
		}
	}
?>