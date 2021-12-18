<?php
	namespace Tilwa\Flows\Structures;

	use Tilwa\Response\Format\AbstractRenderer;

	use DateTime;

	use DateInterval;

	/**
	 *  This is the smallest unit where the ultimate user related cached information is stored
	*/
	class RouteUserNode {

		private $renderer, $hits = 0,

		$maxHitsHydrator = function() {

			return 1;
		},

		$expiresAtHydrator = function () {

			return (new DateTime)->add(new DateInterval("PT10M")); // store for 10 minutes
		};
		
		public function __construct(AbstractRenderer $renderer) {

			$this->renderer = $renderer;
		}

		public function currentHits():int {

			return $this->hits;
		}

		public function getMaxHits(string $userId, string $pattern):int {
			
			return $this->maxHitsHydrator($userId, $pattern);
		}

		public function incrementHits():void {

			$this->hits++;
		}

		public function getExpiresAt(string $userId, string $pattern):DateTime {
			
			return $this->expiresAtHydrator($userId, $pattern);
		}

		/**
		 * @param {callback} => Function (string $userId, string $pattern):DateTime
		*/
		public function setExpiresAtHydrator(callable $callback):self {
			
			$this->expiresAtHydrator = $callback;

			return $this;
		}

		/**
		 * @param {callback} => Function (string $userId, string $pattern):int
		*/
		public function setMaxHitsHydrator(callable $callback):self {
			
			$this->maxHitsHydrator = $callback;

			return $this;
		}

		public function getRenderer():AbstractRenderer {
			
			return $this->renderer;
		}
	}
?>