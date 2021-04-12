<?php
	namespace Tilwa\Flows\Previous;

	use Tilwa\Flows\ControllerFlows;

	class ResponseBuilderProxy {

		private $parentContext;

		function __construct(ControllerFlows $parentContext) {

			$this->parentContext = $parentContext;
		}
		
		public function collectionNode(string $nodeName):CollectionNode {

			return new CollectionNode($this->parentContext, $nodeName);
		}
		
		public function getNode(string $nodeName):SingleNode {

			return new SingleNode($this->parentContext, $nodeName);
		}
	}
?>