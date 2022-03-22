<?php
	namespace Tilwa\Exception\Explosives;

	use Tilwa\Contracts\Requests\ValidationEvaluator;

	use Exception;

	class ValidationFailure extends Exception {

		private $evaluator;

		public function __construct (ValidationEvaluator $evaluator) {

			$this->evaluator = $evaluator;
		}

		public function getEvaluator ():ValidationEvaluator {

			return $this->evaluator;
		}
	}
?>