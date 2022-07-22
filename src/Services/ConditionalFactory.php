<?php
	namespace Suphle\Services;

	use Suphle\Services\Structures\UseCase;

	use Suphle\Exception\Explosives\Generic\InvalidImplementor;

	abstract class ConditionalFactory {

		private $factoryList = [];

		/**
		 * There's a user-defined method with custom type-hinting. That's where dev gets to define their [whenCase] stack that will run arguments that will be eventually injected at consumption point
		*/
		abstract protected function manufacturerMethod ():string;

		/**
		 * Interface implemented by all the use-cases
		*/
		abstract protected function getInterface ():string;

		protected function whenCase(callable $condition, string $handlingClass, ...$classArguments):self {

			$interfaceName = $this->getInterface();

			if (!is_a($handlingClass, $interfaceName, true))

				throw new InvalidImplementor ($interfaceName, $handlingClass);

			$this->factoryList[$handlingClass] = new UseCase($condition, $classArguments);

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

			call_user_func_array([$this, $this->manufacturerMethod()], $arguments);
			
			foreach ($this->factoryList as $handler => $case)

				if ($case->build())

					return new $handler(...$case->getArguments());
		}
	}
?>