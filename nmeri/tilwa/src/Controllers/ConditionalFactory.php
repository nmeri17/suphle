<?php

	namespace Tilwa\Controllers;

	use Tilwa\Controllers\Structures\UseCase;

	abstract class ConditionalFactory {

		private $factoryList = [];

		abstract public function manufacture ();

		// Interface implemented by all the use-cases
		abstract protected function getInterface ():string;

		protected function whenCase(callable $condition, string $handlingClass, ...$arguments):self {

			if ($handlingClass instanceof $this->getInterface())

				$this->factoryList[$handlingClass] = new UseCase($condition, $arguments);

			return $this;
		}

		protected function evaluate() {
			
			foreach ($this->factoryList as $handler => $case)

				if ($case->build())

					return new $handler(...$case->getArguments());
		}

		public function getFactory ():array {

			return $this->factoryList;
		}
	}
?>