<?php
	namespace Tilwa\Flows\Previous;

	class ResponseBuilderProxy {
		
		public function collectionNode(string $nodeName, string $columnName = "id"):CollectionNode {

			return new CollectionNode($nodeName, $columnName);
		}
		
		public function getNode(string $nodeName):SingleNode {

			return new SingleNode($nodeName);
		}
	}
?>