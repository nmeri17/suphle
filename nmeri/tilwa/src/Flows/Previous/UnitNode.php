<?php

	namespace Tilwa\Flows\Previous;

	abstract class UnitNode {

		private $actions = []; // on CollectionNodes, this is the list of actions to take, while on SingleNodes, this is the list of attributes applied

		private $nodeName; // the key on the previous response body this node is attached to

		private $config = [];

		const TTL = 1;

		const MAX_HITS = 2;

		public function getActions():array {
			
			return $this->actions;
		}
		
		public function getNodeName():string {

			return $this->nodeName;
		}
		
		/**
		* @param {callback} int Function(string $userId, string $pattern)
		*/
		public function setTTL(callable $callback):self {

			$this->config[self::TTL] = $callback;

			return $this;
		}
		
		/**
		*	Expire cache contents after this value elapses
		*
		*	@param {callback} int Function(string $userId, string $pattern)
		*/ 
		public function setMaxHits(callable $callback):self {

			$this->config[self::MAX_HITS] = $callback;
			
			return $this;
		}

		public function getConfig():array {
			
			return $this->config;
		}
	}
?>