<?php
	namespace Suphle\Flows\Previous;

	use Suphle\Flows\Structures\{RangeContext, ServiceContext};

	class CollectionNode extends UnitNode {

		private $leafName;

		const PIPE_TO = 12, IN_RANGE = 13, DATE_RANGE = 14, ONE_OF = 15,

		FROM_SERVICE = 16;

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

		/**
		 * Behaves like a singleNode in that the service won't be called repeatedly. Whatever value is retrieved from the payload is handed over, verbatim, to the underlying service
		*/
		public function setFromService(ServiceContext $context):self {

			$this->actions[self::FROM_SERVICE] = $context;

			return $this;
		}

		public function getLeafName ():string {

			return $this->leafName;
		}

		/**
		 * Indicates that the active handler is responsible for pulling content from the previous node, and as such, the collectionNode handler shouldn't bother
		*/
		public function deferExtraction ():bool {

			return array_key_exists(self::FROM_SERVICE, $this->actions);
		}
	}
?>