<?php
	namespace Suphle\Server\Structures;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Server\DependencyFileHandler;

	use Closure;

	class DependencyRule {

		public function __construct (

			protected readonly string $ruleHandler, 

			protected readonly Closure $filterClassName, 

			protected readonly array $argumentList
		) {

			//
		}

		public function shouldEvaluateClass (string $className):bool {

			return call_user_func($this->filterClassName, $className);
		}

		public function extractHandler (Container $container):DependencyFileHandler {

			$handler = $container->getClass($this->ruleHandler);

			$handler->setRunArguments($this->argumentList);

			return $handler;
		}
	}
?>