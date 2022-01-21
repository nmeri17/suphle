<?php
	namespace Tilwa\Hydration;

	use Tilwa\Contracts\Hydration\DecoratorChain;

	use Tilwa\Contracts\Hydration\ScopeHandlers\{ModifiesArguments, ModifyInjected};
	
	use Tilwa\Hydration\Templates\AvoidConstructor;

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

		public function scopeArguments (string $entityName, array $argumentList):array {

			$scope = $this->argumentScope;

			$relevantDecors = $this->getRelevantDecors($scope, $entityName);

			if (empty($relevantDecors)) return $argumentList;

			$noConstructor = $this->container->genericFactory(
				AvoidConstructor::class, 

				["target" => $entityName ],

				function ($types) {

			    	return new AvoidConstructor;
				}
			);

			foreach ($relevantDecors as $decorator) {

				$handler = $this->container->getClass($scope[$decorator]);

				$argumentList = $handler->transformList ($noConstructor, $argumentList);
			}

			return $argumentList;
		}

		private function getRelevantDecors (array $context, string $search):array {

			return array_intersect(array_keys($context), class_implements($search));
		}

		public function scopeInjecting ($concrete) {

			$scope = $this->injectScope;

			$relevantDecors = $this->getRelevantDecors($scope, get_class($concrete));

			if (empty($relevantDecors)) return $concrete;

			foreach ($relevantDecors as $decorator) {

				$handler = $this->container->getClass($scope[$decorator]);

				$concrete = $handler->upgradeInstance ($concrete);
			}

			return $concrete;
		}
	}
?>