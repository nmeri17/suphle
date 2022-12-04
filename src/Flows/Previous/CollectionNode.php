<?php
	namespace Suphle\Flows\Previous;

	use Suphle\Flows\Structures\{RangeContext, ServiceContext};

	class CollectionNode extends UnitNode {

		final const PIPE_TO = 12;
  final const IN_RANGE = 13;
  final const DATE_RANGE = 14;
  final const AS_ONE = 15;
  final const FROM_SERVICE = 16;

		public function __construct(string $nodeName, private readonly string $leafName) {

			$this->nodeName = $nodeName;
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
		*/
		public function asOne ():self {

			$this->actions[self::AS_ONE] = $this->leafName . "s";

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