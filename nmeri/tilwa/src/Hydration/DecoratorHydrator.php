<?php
	namespace Tilwa\Hydration;

	use Tilwa\Contracts\Hydration\DecoratorChain;

	use Tilwa\Contracts\Hydration\ScopeHandlers\{ModifiesArguments, ModifyInjected};

	use Tilwa\Hydration\Structures\ObjectDetails;
	
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

			$this->argumentScope = array_filter($this->chain, function ($decorator) {

				return $this->objectMeta->implementsInterface($decorator, ModifiesArguments::class);
			});

			$this->injectScope = array_filter($this->chain, function ($decorator) {

				return $this->objectMeta->implementsInterface($decorator, ModifyInjected::class);
			});
		}

		public function scopeArguments (string $entityName, array $argumentList, string $methodName):array {

			$scope = $this->argumentScope;

			$container = $this->container;

			$relevantDecors = $this->getRelevantDecors($scope, $entityName);

			if (empty($relevantDecors)) return $argumentList;

			if ($methodName == Container::CLASS_CONSTRUCTOR) {

				$hasConstructor = true;

				$concrete = $this->noConstructor($entityName);
			}

			else $concrete = $container->getClass($entityName);

			foreach ($relevantDecors as $decorator) {

				$handler = $container->getClass($scope[$decorator]);

				if ($hasConstructor)

					$argumentList = $handler->transformConstructor ($concrete, $argumentList);

				else $argumentList = $handler->transformMethods($concrete, $argumentList);
			}

			return $argumentList;
		}

		/**
		 * @return numerically indexed names of matching decorators
		*/
		private function getRelevantDecors (array $context, string $search):array {

			return array_intersect(

				array_keys($context), class_implements($search)
			);
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