<?php
	namespace Tilwa\Flows\Previous;

	class ResponseBuilderProxy {
		
		public function collectionNode(string $nodeName):CollectionNode {

			return new CollectionNode($nodeName);
		}
		
		public function getNode(string $nodeName):SingleNode {

			return new SingleNode($nodeName);
		}
	}
?>