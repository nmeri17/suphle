<?php
	namespace Tilwa\Flows;

	// represents a meta map of actions to take on a previous response node when it's hydrated
	class CollectionNode {

		const EACH_ATTRIBUTE = 1; // expects these methods to be called in a meaningful sequence

		const PIPE_TO = 2;

		const IN_RANGE = 3;

		const DATE_RANGE = 4;

		const ONE_OF = 5;

		private $actions;

		private $parentContext; // could be used to populate this guy when a node should create multiple copies in its parent

		private $nodeName; // the node on the previous response body this object is attached to

		function __construct(ControllerFlows $parentContext, string $nodeName) {
			
			$this->actions = [];

			$this->parentContext = $parentContext;

			$this->nodeName = $nodeName;
		}

		/*// we still need this
		public function interactsWithDatabase():bool; // only applicable to fetch routes*/
		
		 // works like `reduce`
		public function eachAttribute(string $attribute):self {

			$this->actions[self::EACH_ATTRIBUTE] = $attribute;

			return $this;
		}
		
		public function pipeTo(string $service, string $method):self {

			$this->actions[self::PIPE_TO] = compact("service", "method");

			return $this;
		}
		
		// same as [pipeTo], but is sent in bulk to the service rather than one after the other. service is expected to do a `whereIn`
		// during fetch, we pull just those matching [key] instead of creating multiple copies of the node in the sibling list
		public function oneOf(string $service, string $method):self {

			$this->actions[self::ONE_OF] = compact("service", "method");

			return $this;
		}
		
		// this and [dateRange] will plug each of the values they receive from the flow hydrator into the services supplied
		public function inRange(string $service, string $method):self {

			$this->actions[self::IN_RANGE] = compact("service", "method");

			return $this;
		}
		
		public function dateRange(string $service, string $method):self {

			$this->actions[self::DATE_RANGE] = compact("service", "method");

			return $this;
		}
	}
?>