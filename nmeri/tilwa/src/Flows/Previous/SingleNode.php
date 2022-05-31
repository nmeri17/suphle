<?php
	namespace Tilwa\Flows\Previous;

	class SingleNode extends UnitNode {

		const ALTERS_QUERY_SEGMENT = 1;

		function __construct(string $nodeName) {

			$this->nodeName = $nodeName;
		}
		
		public function altersQuery ():self {

			$this->actions[self::ALTERS_QUERY_SEGMENT] = null;

			return $this;
		}
	}
?>