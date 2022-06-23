<?php
	namespace Tilwa\Exception\Explosives;

	use Tilwa\Contracts\Requests\ValidationEvaluator;

	use Exception;

	class ValidationFailure extends Exception {

		private $evaluator;

		public function __construct (ValidationEvaluator $evaluator) {

			$this->evaluator = $evaluator;

			$this->message = json_encode(

				$this->evaluator->getValidatorErrors(), JSON_PRETTY_PRINT
			); // using this since error handler will be stubbed out in tests, thus precluding us from seeing what failed
		}

		public function getEvaluator ():ValidationEvaluator {

			return $this->evaluator;
		}
	}
?>