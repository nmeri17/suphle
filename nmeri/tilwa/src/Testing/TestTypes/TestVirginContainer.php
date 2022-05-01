<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\{Structures\BaseInterfaceCollection, DecoratorHydrator, InterfaceHydrator};

	use Tilwa\Testing\Condiments\MockFacilitator;

	use PHPUnit\Framework\TestCase;

	class TestVirginContainer extends TestCase {

		use MockFacilitator;

		protected function bootContainer ($container):void {

			$container->initializeUniversalProvision();

			$container->provideSelf();
		}

		protected function stubDecorator ():DecoratorHydrator {

			return $this->positiveDouble(DecoratorHydrator::class, [

				"scopeArguments" => $this->returnArgument(1),

				"scopeInjecting" => $this->returnArgument(0)
			]);
		}

		protected function withDefaultInterfaceCollection ($container):void {

			$container->setInterfaceHydrator(BaseInterfaceCollection::class);
		}

		protected function stubbedInterfaceCollection ():InterfaceHydrator {

			return $this->positiveDouble(InterfaceHydrator::class, [

				"deriveConcrete" => $this->returnCallback(function ($subject) {

					return $this->positiveDouble($subject, []);
				})
			]);
		}

		protected function dataProvider (array $callables, callable $testBody):void {

			foreach ($callables as $provider)

				foreach ($provider() as $dataFixture)

					$testBody(...$dataFixture);
		}
	}
?>