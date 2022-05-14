<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\{Structures\BaseInterfaceCollection, DecoratorHydrator, InterfaceHydrator};

	use Tilwa\Testing\Condiments\MockFacilitator;

	use PHPUnit\Framework\{TestCase, ExpectationFailedException};

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

		/**
		 * @param {callables} Expects them all to be methods, not closures or anonymous methods
		*/
		protected function dataProvider (array $callables, callable $testBody):void {

			foreach ($callables as $provider) {

				foreach ($provider() as $index => $dataFixture)

					try { $testBody(...$dataFixture); }

					catch (ExpectationFailedException $exception) {

						echo $this->providerExceptionMessage($provider, $index, $dataFixture);

						throw $exception;
					}
			}
		}

		private function providerExceptionMessage (array $providerCallable, int $errorIndex, array $dataRow):string {

			$newLine = "\n";

			$providerName = get_class($providerCallable[0]) . "::". $providerCallable[1];

			$messages = [
				"Failed test with data provider '$providerName', on index $errorIndex:",

				json_encode($dataRow, JSON_PRETTY_PRINT)
			];

			return $newLine. implode($newLine, $messages). $newLine;
		}
	}
?>