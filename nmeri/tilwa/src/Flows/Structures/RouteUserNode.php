<?php

	namespace Tilwa\Flows\Structures;

	use Tilwa\Response\Format\AbstractRenderer;

	use DateTime;

	use DateInterval;

	// this is the smallest unit where the ultimate user related cached information is stored
	class RouteUserNode {

		private $renderer, $hits, $maxHitsHydrator,

		$expiresAtHydrator;
		
		function __construct(AbstractRenderer $renderer) {

			$this->renderer = $renderer;

			$this->setDefaultHydrators();
		}

		public function currentHits():int {

			return $hits;
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

		public function setExpiresAtHydrator(callable $callback):self {
			
			$this->expiresAtHydrator = $callback;

			return $this;
		}

		public function setMaxHitsHydrator(callable $callback):self {
			
			$this->maxHitsHydrator = $callback;

			return $this;
		}

		public function getRenderer():AbstractRenderer {
			
			return $this->renderer;
		}

		private function setDefaultHydrators():void {
			
			$this->expiresAtHydrator = function () {

				return (new DateTime)

				->add(new DateInterval("PT1M")); // store for a minute
			};
			
			$this->maxHitsHydrator = function() {

				return 1;
			};
		}
	}
?>