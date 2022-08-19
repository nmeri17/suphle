<?php
	namespace Suphle\Hydration;

	use Suphle\Contracts\Hydration\DecoratorChain;

	use Suphle\Contracts\Hydration\ScopeHandlers\{ModifiesArguments, ModifyInjected};

	use Suphle\Hydration\Structures\ObjectDetails;
	
	use ReflectionClass;

	class DecoratorHydrator {

		private $chain, $argumentScope, $injectScope,

		$container, $objectMeta;

		public function __construct (Container $container, DecoratorChain $chain, ObjectDetails $objectMeta) {

			$this->chain = $chain->allScopes();

			$this->container = $container;

			$this->objectMeta = $objectMeta;
		}

		public function assignScopes ():void {

			$this->argumentScope = array_filter($this->chain, function ($handler) {

				return $this->objectMeta->implementsInterface($handler, ModifiesArguments::class);
			});

			$this->injectScope = array_filter($this->chain, function ($handler) {

				return $this->objectMeta->implementsInterface($handler, ModifyInjected::class);
			});
		}

		public function scopeArguments (string $entityName, array $argumentList, string $methodName):array {

			$scope = $this->argumentScope;

			$container = $this->container;

			$relevantDecors = $this->getRelevantDecors($scope, $entityName);

			if (empty($relevantDecors)) return $argumentList;

			$hasConstructor = false;

			if ($methodName == Container::CLASS_CONSTRUCTOR) {

				$hasConstructor = true;

				$concrete = $this->noConstructor($entityName);
			}

			else $concrete = $container->getClass($entityName);

			foreach ($relevantDecors as $decorator) {

				$handler = $container->getClass($scope[$decorator]);

				if ($hasConstructor)

					$argumentList = $handler->transformConstructor ($concrete, $argumentList);

				else $argumentList = $handler->transformMethods($concrete, $argumentList, $methodName);
			}

			return $argumentList;
		}

		/**
		 * @return numerically indexed names of matching decorators
		*/
		public function getRelevantDecors (array $context, string $search):array {

			$active = $this->objectMeta->parentInterfaceMatches(

				$search, array_keys($context)
			);

			$unique = $active; // one decorator extending another should not select super handler since it's likely already used in the sub

			foreach ($active as $decorator) {

				$parents = $this->objectMeta->parentInterfaceMatches(

					$decorator, $active // safe not to omit self since it's not its own parent
				);

				if (!empty($parents)) // weed out preceding ancestors

					$unique = array_diff($unique, $parents);
			}

			return array_values($unique);
		}

		/**
		 * @return given class instance, but avoids calling its constructor */
		private function noConstructor (string $className) {

			return (new ReflectionClass($className))->newInstanceWithoutConstructor();
		}

		public function scopeInjecting (object $concrete, string $caller) {

			$scope = $this->injectScope;

			$relevantDecors = $this->getRelevantDecors($scope, get_class($concrete));

			foreach ($relevantDecors as $decorator)

				$concrete = $this->container

				->getClass($scope[$decorator])

				->examineInstance($concrete, $caller);

			return $concrete;
		}
	}
?>