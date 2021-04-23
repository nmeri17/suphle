<?php

	namespace Tilwa\Flows\Previous;

	abstract class UnitNode {

		private $actions = []; // on CollectionNodes, this is the list of actions to take, while on SingleNodes, this is the list of attributes applied

		private $nodeName; // the key on the previous response body this node is attached to

		const SURVIVE_FOR = 12;

		public function getActions():array {
			
			return $this->actions;
		}
		
		public function getNodeName():string {

			return $this->nodeName;
		}
		
		// means this won't be cleared on hit and won't be loaded by subsequent flows matching the same builder
		public function surviveFor(int $duration ):self {

			$this->actions[self::SURVIVE_FOR] = $duration;

			return $this;
		}
	}
?>