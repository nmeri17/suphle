<?php
	namespace Suphle\Exception\Explosives;

	use Suphle\Contracts\Requests\ValidationEvaluator;

	use Exception;

	class ValidationFailure extends Exception {

		private $evaluator;

		public function __construct (ValidationEvaluator $evaluator) {

			$this->evaluator = $evaluator;

			$this->message = json_encode(

				$evaluator->getValidatorErrors(), JSON_PRETTY_PRINT
			); // assigning here otherwise assertion failure will preclude seeing what failed
		}

		public function getEvaluator ():ValidationEvaluator {

			return $this->evaluator;
		}
	}
?>