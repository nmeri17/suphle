<?php
	namespace Tilwa\Flows;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	class ControllerFlows {

		const DATE_ON_HIT = 1;

		private $branches; // this is the guy containing all the information the hydrator is interested in

		private $actions;

		function __construct() {

			$this->actions = [];

			$this->branches = [];
		}

		private function linksTo(string $pattern, $responseStructure):self {

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
	}
?>