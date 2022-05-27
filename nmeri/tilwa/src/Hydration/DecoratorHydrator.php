<?php
	namespace Tilwa\Hydration;

	use Tilwa\Contracts\Hydration\DecoratorChain;

	use Tilwa\Contracts\Hydration\ScopeHandlers\{ModifiesArguments, ModifyInjected};

	use Tilwa\Hydration\Structures\{ObjectDetails, DecoratorCallResult};

	use ProxyManager\Factory\AccessInterceptorValueHolderFactory as AccessInterceptor;
	
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

			// $callResult

			foreach ($relevantDecors as $decorator) { // we want to pass previous accessor or something so result of a method call doesn't get lost

				$handler = $this->container->getClass($scope[$decorator]);

				$callResult = new DecoratorCallResult($concrete);

				$handler->getOriginAccessor()->setCallDetails(

					$callResult, $caller
				);

				$concrete = (new AccessInterceptor)->createProxy( // using this library to abstract away proxy creating process

					$callResult->getConcrete(),

					$this->delegateHookToHandler( $handler->methodPreHooks())
					// no argument 3 since we don't care about post hooks
				);
			}
// then extract here
			return $concrete;
		}

		private function delegateHookToHandler (array $methodHooks):array {

			$vitals = [];

			foreach ($methodHooks as $hooker => $preMethodAction) // handlers with same method won't clss since we're using unique proxies for each handler

				$vitals[$hooker] = function ($proxy, $concrete, $calledMethod, $parameters, &$earlyReturn) {

					$earlyReturn = true; // since final handler will be responsible for calling final method, not this library

					/*
						$preMethodAction = function (array $argumentList):DecoratorCallResult
					*/
					return $preMethodAction($parameters);
				};

			return $vitals;
		}
	}
?>