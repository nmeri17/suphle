<?php

	namespace Tilwa\Flows\Structures;

	class RangeContext {

		private $parameterMax = "max",

		$parameterMin = "min",

		$between = true; // get only the 2 edges or everything in between

		function __construct(string $parameterMax, string $parameterMin, bool $between) {

			$this->parameterMax = $parameterMax;

			$this->parameterMin = $parameterMin;

			$this->between = $between;
		}

		public function getParameterMax():string {
			
			return $this->parameterMax;
		}

		public function getParameterMin():string {
			
			return $this->parameterMin;
		}

		public function getBetween():bool {
			
			return $this->between;
		}
	}
?>