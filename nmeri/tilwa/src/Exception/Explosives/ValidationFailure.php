<?php
	namespace Tilwa\Exception\Explosives;

	use Tilwa\Contracts\{Requests\ValidationEvaluator, Exception\ContextualException};

	use Exception;

	class ValidationFailure extends Exception implements ContextualException {

		private $evaluator;

		public function __construct (ValidationEvaluator $evaluator) {

			$this->evaluator = $evaluator;
		}

		public function getContext ():array {

			return ["evaluator" => $this->evaluator];
		}
	}
?>