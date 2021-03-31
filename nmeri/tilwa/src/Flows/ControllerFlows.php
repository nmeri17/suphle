<?php
	namespace Tilwa\Flows;

	use Tilwa\Http\Response\Format\AbstractRenderer;

	class ControllerFlows {

		const DATE_ON_HIT = 1;

		private $renderer; // we'll record the eventual builder on a property on this guy. he's the one who gets saved ultimately

		private $siblings;

		private $actions;

		function __construct(AbstractRenderer $renderer) {
			
			$this->renderer = $renderer;

			$this->actions = [];

			$this->siblings = [];
		}

		private function linksTo(string $pattern, $responseStructure):self {

			$this->siblings[$pattern] = $responseStructure;

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