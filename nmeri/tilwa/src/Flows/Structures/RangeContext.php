<?php

	namespace Tilwa\Flows\Structures;

	class RangeContext {

		private $parameterMax = "max",

		$parameterMin = "min";

		function __construct(string $parameterMax, string $parameterMin) {

			$this->parameterMax = $parameterMax;

			$this->parameterMin = $parameterMin;
		}

		public function getParameterMax():string {
			
			return $this->parameterMax;
		}

		public function getParameterMin():string {
			
			return $this->parameterMin;
		}
	}
?>