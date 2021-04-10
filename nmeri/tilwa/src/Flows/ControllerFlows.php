<?php
	namespace Tilwa\Flows;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	class ControllerFlows {

		const DATE_ON_HIT = 1;

		private $branches; // this is the guy containing all the information the hydrator is interested in

		private $actions;

		private $config;

		function __construct() {

			$this->actions = [];

			$this->branches = [];

			$this->config = [];
		}

		public function linksTo(string $pattern, $responseStructure):self {

			$this->branches[$pattern] = $responseStructure;

			return $this;
		}

		public function previousResponse():ResponseBuilderProxy {

			return new ResponseBuilderProxy($this);
		}

		// evaluated at runtime
		public function dateOnHit(string $dateFormat):self {

			$this->actions[self::DATE_ON_HIT] = $dateFormat;
		}

		/**
		* @param {sourceService} where we'll be pulling the data we intend to filter into another operation
		*/
		public function fromService(string $sourceService, string $method, SingleNode $responseBuilder):CollectionNode {

			return new CollectionNode($this, $responseBuilder->getNodeName());
		}
		
		/**
		*
		* @param {callback} Function(string $userId, string $pattern)
		* @return int in seconds*/
		public function setTTL(callable $callback):void /*int*/ {

			$this->config["ttl"] = $callback;
			
			// return 60; // goes to the getter. These arguments ought to be plugged in
		}
		
		// expire cache contents after this value elapses
		public function setMaxHits(callable $callback):void /*int*/ {

			$this->config["max_hits"] = $callback;
			
			// return 1;
		}
	}
?>