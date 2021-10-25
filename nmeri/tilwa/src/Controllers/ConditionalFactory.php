<?php

	namespace Tilwa\Controllers;

	use Tilwa\Controllers\Structures\UseCase;

	abstract class ConditionalFactory {

		private $factoryList = [];

		/**
		 * This features the [whenCase] stack that runs against that argument
		*/
		abstract protected function manufacture (...$arguments);

		// Interface implemented by all the use-cases
		abstract protected function getInterface ():string;

		protected function whenCase(callable $condition, string $handlingClass, ...$arguments):self {

			if ($handlingClass instanceof $this->getInterface())

				$this->factoryList[$handlingClass] = new UseCase($condition, $arguments);

			return $this;
		}

		/**
		 * This is the method user calls from their controller
		*/
		public function retrieveConcrete(...$arguments) {

			$this->manufacture(...$arguments);
			
			foreach ($this->factoryList as $handler => $case)

				if ($case->build())

					return new $handler(...$case->getArguments());
		}

		public function getFactory ():array {

			return $this->factoryList;
		}
	}
?>