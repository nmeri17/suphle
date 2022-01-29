<?php
	namespace Tilwa\Hydration;

	use Tilwa\Contracts\Hydration\DecoratorChain;

	use Tilwa\Contracts\Hydration\ScopeHandlers\{ModifiesArguments, ModifyInjected};
	
	use ReflectionClass;

	class DecoratorHydrator {

		private $chain, $argumentScope, $injectScope,

		$container;

		public function __construct (Container $container, DecoratorChain $chain) {

			$this->chain = $chain->allScopes();

			$this->container = $container;
		}

		public function assignScopes ():void {

			$this->argumentScope = array_filter($this->chain, function ($decorator)) {

				return $decorator instanceof ModifiesArguments;
			});

			$this->injectScope = array_filter($this->chain, function ($decorator)) {

				return $decorator instanceof ModifyInjected;
			});
		}

		public function scopeArguments (string $entityName, array $argumentList, string $methodName):array {

			$scope = $this->argumentScope;

			$container = $this->container;

			$relevantDecors = $this->getRelevantDecors($scope, $entityName);

			if (empty($relevantDecors)) return $argumentList;

			if ($methodName == "__construct") $hasConstructor = true;

			if ($hasConstructor)

				$concrete = $this->noConstructor($entityName);

			else $concrete = $container->getClass($entityName);

			foreach ($relevantDecors as $decorator) {

				$handler = $container->getClass($scope[$decorator]);

				if ($hasConstructor)

					$argumentList = $handler->transformConstructor ($concrete, $argumentList);

				else $argumentList = $handler->transformMethods($concrete, $argumentList);
			}

			return $argumentList;
		}

		private function getRelevantDecors (array $context, string $search):array {

			return array_intersect(array_keys($context), class_implements($search));
		}

		/**
		 * @return given class instance, but avoids calling its constructor */
		private function noConstructor (string $className) {

			return (new ReflectionClass($className))->newInstanceWithoutConstructor();
		}

		public function scopeInjecting ($concrete, string $caller) {

			$scope = $this->injectScope;

			$relevantDecors = $this->getRelevantDecors($scope, get_class($concrete));

			if (empty($relevantDecors)) return $concrete;

			foreach ($relevantDecors as $decorator) {

				$handler = $this->container->getClass($scope[$decorator]);

				$concrete = $handler->proxifyInstance ($concrete, $caller);
			}

			return $concrete;
		}
	}
?>