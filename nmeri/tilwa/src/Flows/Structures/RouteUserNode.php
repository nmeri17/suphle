<?php

	namespace Tilwa\Flows\Structures;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	use DateTime;

	// this is the smallest unit where the ultimate user related cached information is stored
	class RouteUserNode {

		private $renderer, $hits;
		
		function __construct(AbstractRenderer $renderer) {

			$this->renderer = $renderer;
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