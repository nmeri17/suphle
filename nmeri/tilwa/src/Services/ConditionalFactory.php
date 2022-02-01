<?php
	namespace Tilwa\Services;

	use Tilwa\Services\Structures\UseCase;

	use Tilwa\Exception\Explosives\Generic\InvalidImplementor;

	abstract class ConditionalFactory {

		private $factoryList = [];

		/**
		 * This features the [whenCase] stack that runs against that argument
		*/
		abstract protected function manufacture (...$arguments):void;

		/**
		 * Interface implemented by all the use-cases
		*/
		abstract protected function getInterface ():string;

		protected function whenCase(callable $condition, string $handlingClass, ...$classArguments):self {

			if ($handlingClass instanceof $this->getInterface())

				$this->factoryList[$handlingClass] = new UseCase($condition, $classArguments);

			else throw new InvalidImplementor ($this->getInterface(), $handlingClass);

			return $this;
		}

		protected function finally ( string $handlingClass, ...$classArguments):void {

			$this->whenCase(function () {

				return true;
			}, $handlingClass, ...$classArguments);
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
	}
?>