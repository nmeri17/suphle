<?php
	namespace Tilwa\Flows\Previous;

	use Tilwa\Flows\Structures\{RangeContext, ServiceContext};

	class CollectionNode extends UnitNode {

		private $leafName;

		const PIPE_TO = 2;

		const IN_RANGE = 3;

		const DATE_RANGE = 4;

		const ONE_OF = 5;

		const FROM_SERVICE = 6;

		public function __construct(string $nodeName, string $leafName) {

			$this->nodeName = $nodeName;

			$this->leafName = $leafName;
		}
		
		/**
		 * Will create multiple versions of the node attached. When a request where the wildcard matches the key passed in [leafName], we return the key corresponding to what was evaluated here
		*/
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
		
		public function inRange (RangeContext $context = null):self {

			$this->actions[self::IN_RANGE] = $context ?? new RangeContext;

			return $this;
		}
		
		public function dateRange (RangeContext $context = null):self {

			$this->actions[self::DATE_RANGE] = $context ?? new RangeContext;

			return $this;
		}

		public function setFromService(ServiceContext $context):self {

			$this->actions[self::FROM_SERVICE] = $context;

			return $this;
		}

		public function getLeafName ():string {

			return $this->leafName;
		}
	}
?>