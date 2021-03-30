<?php
	namespace Tilwa\Flows;

	use Tilwa\Http\Response\BaseResponseManager;

	use Tilwa\Events\EventManager;

	class FlowFinder extends BaseResponseManager {

		private $eventManager;

		function __construct(EventManager $eventManager) {
			
			$this->eventManager = $eventManager;
		}

		public function matchesRequest():bool {
			
			//
		}

		public function flowExists():bool {
			
			//
		}

		public function getResponse():string {

			$context = $this->getActiveFlow();

			$renderer = $context->hydrateRenderer();

			$controllerManager = $this->getControllerManager($renderer);

			$renderer->invokeActionHandler($controllerManager->getHandlerParameters());

			$body = $renderer->render();

			return $body;
		}

		public function queueNext($nextResponse):void { // should include the full renderer as well
			
			//
		}

		private function getActiveFlow() {
			
			// find the flow request in the cache matching current get parameters
		}
	}
?>