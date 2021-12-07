<?php
	namespace Tilwa\Testing\Condiments;

	use Prophecy\Prophet;

	trait ProphecyWrapper {

		protected $prophet;

		protected function setUp () {

			$this->prophet = new Prophet;
		}

		protected function tearDown () {

			$this->prophet->checkPredictions();
		}

		/**
		 * You call [reveal] and set the expectations on the instance returned from here
		*/
		protected function prophesize (string $class) {

			return $this->prophet->prophesize($class);
		}
	}
?>