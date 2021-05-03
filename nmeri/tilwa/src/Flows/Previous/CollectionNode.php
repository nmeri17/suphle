<?php
	namespace Tilwa\Flows\Previous;

	use Tilwa\Flows\Structures\{RangeContext, ServiceContext};

	// represents a meta map of actions to take on a previous response node when it's hydrated
	class CollectionNode extends UnitNode {

		const EACH_ATTRIBUTE = 1; // expects these methods to be called in a meaningful sequence

		const PIPE_TO = 2;

		const IN_RANGE = 3;

		const DATE_RANGE = 4;

		const ONE_OF = 5;

		const FROM_SERVICE = 6;

		function __construct(string $nodeName) {

			$this->nodeName = $nodeName;
		}
		
		 // works like `reduce`. should recursively load [attribute] but only match request to the last one?
		public function eachAttribute(string $attribute):self {

			$this->actions[self::EACH_ATTRIBUTE] = $attribute;

			return $this;
		}
		
		// will create multiple versions of the node attached. when a request where the wildcard matches the key passed in [eachAttribute], we return the key corresponding to what was evaluated here
		public function pipeTo():self {

			$this->actions[self::PIPE_TO] = 1;

			return $this;
		}
		
		/**
		*	same as [pipeTo], but is sent in bulk to the service rather than one after the other. service is expected to do a `whereIn`
		*	@param {parameterId} property name on the handling request to set the ids to
		*/
		public function oneOf(string $parameterId = "ids"):self {

			$this->actions[self::ONE_OF] = $parameterId;

			return $this;
		}
		
		// this and [dateRange] will plug each of the values they receive from the flow hydrator into the services supplied
		public function inRange(RangeContext $context):self {

			$this->actions[self::IN_RANGE] = $context ?? new RangeContext;

			return $this;
		}
		
		public function dateRange(RangeContext $context):self {

			$this->actions[self::DATE_RANGE] = $context ?? new RangeContext;

			return $this;
		}

		public function setFromService(ServiceContext $context):self {

			$this->actions[self::FROM_SERVICE] = $context;

			return $this;
		}
	}
?>