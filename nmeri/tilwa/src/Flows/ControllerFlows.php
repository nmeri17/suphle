<?php
	namespace Tilwa\Flows;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	use Tilwa\Flows\Previous\{ResponseBuilderProxy, SingleNode, CollectionNode, UnitNode};

	class ControllerFlows {

		private $branches = [];

		private $config = [];

		public function linksTo(string $pattern, UnitNode $responseStructure):self {

			$this->branches[$pattern] = $responseStructure;

			return $this;
		}

		public function previousResponse():ResponseBuilderProxy {

			return new ResponseBuilderProxy();
		}

		public function eachBranch(callable $callback) {
			
			foreach ($this->branches as $path => $branch) {
				
				$callback($path, $branch);
			}
		}

		/**
		* @param {sourceService} where we'll be pulling the data we intend to filter into another operation
		*/
		public function fromService(string $sourceService, string $method, UnitNode $responseBuilder):CollectionNode {

			$node = new CollectionNode( $responseBuilder->getNodeName());

			$node->setFromService($sourceService, $method);

			return $node;
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

		public function getConfig():array {
			
			return $this->config;
		}
	}
?>