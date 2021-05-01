<?php
	namespace Tilwa\Flows\Previous;

	// represents a meta map of actions to take on a previous response node when it's hydrated
	class SingleNode extends UnitNode {

		const INCLUDES_PAGINATION = 1; // expects these methods to be called in a meaningful sequence

		function __construct(string $nodeName) {

			$this->nodeName = $nodeName;
		}
		
		/**
		* @param {nextPagePath} needs the router to find the controller servicing this path
		*/
		public function includesPagination(string $nextPagePath):self {

			$this->actions[self::INCLUDES_PAGINATION] = $nextPagePath;

			return $this;
		}
	}
?>