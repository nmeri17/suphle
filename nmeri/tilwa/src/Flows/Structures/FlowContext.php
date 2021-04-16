<?php

	namespace Tilwa\Flows\Structures;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	use Tilwa\Events\EventManager;

	use DateTime;

	class FlowContext {

		private $contentOwner;

		private $previousResponse;

		private $renderer;

		private $controllerFlow;

		private $payload;

		private $eventManager;

		private $config;

		private $hits;
		
		function __construct(string $contentOwner, $previousResponse, AbstractRenderer $renderer, ControllerFlows $controllerFlow) {
			
			$this->contentOwner = $contentOwner;

			$this->previousResponse = $previousResponse;

			$this->renderer = $renderer;

			$this->flow = $controllerFlow;
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
			
			return $this->controllerFlow->branches;
		}

		public function currentHits():int {

			return $hits;
		}

		public function getMaxHits():void {

			//read from setHits on the controller flows
		}

		public function incrementHits():void {

			$this->hits++;
		}

		public function getExpiresAt():DateTime {
			# code...
		}

		public function getRenderer():AbstractRenderer {
			
			return $this->renderer;
		}
	}
?>