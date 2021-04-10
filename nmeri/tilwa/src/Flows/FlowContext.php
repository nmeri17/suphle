<?php

	namespace Tilwa\Flows;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	use Tilwa\Events\EventManager;

	class FlowContext {

		private $contentOwner;

		private $previousResponse;

		private $renderer;

		private $routeBranches;

		private $payload;

		private $eventManager;

		private $config;
		
		function __construct(string $contentOwner, $previousResponse, AbstractRenderer $renderer, ControllerFlows $routeBranches) {
			
			$this->contentOwner = $contentOwner;

			$this->previousResponse = $previousResponse;

			$this->renderer = $renderer;

			$this->flow = $routeBranches;
		}

		public function getPayload() {
			
			return $this->payload;
		}

		public function setEventManager(EventManager $eventManager):void {

			$this->eventManager = $eventManager;
		}

		public function getEventManager():EventManager {
			
			return $this->eventManager;
		}

		public function getBranches():ControllerFlows {
			
			return $this->routeBranches;
		}
	}
?>