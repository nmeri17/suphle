<?php
	namespace Tilwa\Flows;

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