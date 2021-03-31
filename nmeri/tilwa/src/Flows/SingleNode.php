<?php
	namespace Tilwa\Flows;

	// represents a meta map of attributes to take on a previous response node when it's hydrated
	class SingleNode {

		const INCLUDES_PAGINATION = 1; // expects these methods to be called in a meaningful sequence

		const SURVIVE_FOR = 2;

		private $attributes; // list of attributes to take on the active node

		private $parentContext; // could be used to populate this guy when a node should create multiple copies in its parent

		private $nodeName; // the node on the previous response body this object is attached to

		function __construct(ControllerFlows $parentContext, string $nodeName) {
			
			$this->attributes = [];

			$this->parentContext = $parentContext;

			$this->nodeName = $nodeName;
		}

		/*// we still need this
		public function interactsWithDatabase():bool; // only applicable to fetch routes*/
		
		/**
		* @param {nextPagePath} needs the router to find the controller servicing this path
		*/
		public function includesPagination(string $nextPagePath):self {

			$this->attributes[self::INCLUDES_PAGINATION] = $nextPagePath;

			return $this;
		}
		
		// means this won't be cleared on hit and won't be loaded by subsequent flows matching the same builder
		public function surviveFor(int $duration ):self {

			$this->attributes[self::SURVIVE_FOR] = $duration;

			return $this;
		}
		
		public function getNodeName():string {

			return $this->nodeName;
		}
	}
?>