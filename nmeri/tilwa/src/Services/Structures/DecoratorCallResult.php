<?php
	namespace Tilwa\Services\Structures;

	class DecoratorCallResult {

		private $concrete, $result, $hasCalled;

		public function __construct (object $concrete, $result = null, $hasCalled = false) {

			$this->concrete = $concrete;

			$this->result = $result;

			$this->hasCalled = $hasCalled;
		}

		public function getConcrete ():object {

			return $this->concrete;
		}

		/**
		 * Prevent lower handlers from calling method again
		*/
		public function calledConcrete ():bool {

			return $this->hasCalled;
		}

		public function getResult () {

			return $this->callResult;
		}
	}
?>