<?php
	namespace Suphle\Testing\TestTypes;

	use Suphle\Hydration\{DecoratorHydrator, InterfaceHydrator, Container};

	use Suphle\Hydration\Structures\{BaseInterfaceCollection, ContainerTelescope};

	use Suphle\Testing\Condiments\MockFacilitator;

	use PHPUnit\Framework\{TestCase, ExpectationFailedException};

	use Throwable;

	class TestVirginContainer extends TestCase {

		use MockFacilitator;

		protected $containerTelescope, $monitorContainer = false;

		protected function bootContainer (Container $container):void {

			$container->initializeUniversalProvision();

			$container->setEssentials();
		}

		protected function stubDecorator ():DecoratorHydrator {

			return $this->positiveDouble(DecoratorHydrator::class, [

				"scopeArguments" => $this->returnArgument(1),

				"scopeInjecting" => $this->returnArgument(0)
			]);
		}

		protected function withDefaultInterfaceCollection (Container $container):void {

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
		 * @param {callables} Expects them all to be methods, not closures or anonymous methods. Structure:
		 * 
		 * [$this, method]. Each method
		 * 
		 * [[foo, bar], [foobar, nmeri]]
		*/
		protected function dataProvider (array $callables, callable $testBody):void {

			$this->beforeAllMethods();

			foreach ($callables as $methodIndex => $method) { // between here

				$this->beforeEachMethod($methodIndex);

				foreach ($method() as $fixtureIndex => $dataFixture) {

					try { // and here, don't backup against original to avoid overwriting provider modifications

						$this->beforeEachFixture($fixtureIndex);

						$testBody(...$dataFixture);

						$this->afterEachFixture($fixtureIndex);
					}

					catch (Throwable $exception) { // test failures throw ExpectationFailedException, but without catching errors, error message will appear as if all providers and data sets failed

						echo $this->providerExceptionMessage(

							$method, $fixtureIndex, $dataFixture
						);

						throw $exception;
					}
				}
			}

			$this->afterAllMethods();
		}

		protected function beforeAllMethods ():void {}

		/**
		 * Restore original modules' state
		*/
		protected function beforeEachMethod (int $methodIndex):void {}

		/**
		 * Restore preliminary state
		*/
		protected function beforeEachFixture (int $fixtureIndex):void {}

		protected function afterEachFixture (int $fixtureIndex):void {}

		protected function afterAllMethods ():void {}

		private function providerExceptionMessage (array $methodCallable, int $errorIndex, array $dataRow):string {

			$newLine = "\n";

			$methodName = get_class($methodCallable[0]) . "::". $methodCallable[1];

			$messages = [
				"$methodName with data set #$errorIndex:",

				json_encode($dataRow, JSON_PRETTY_PRINT)
			];

			return $newLine. implode($newLine, $messages). $newLine;
		}

		protected function mayMonitorContainer (Container $container):void {

			if ($this->monitorContainer) {

				if (is_null($this->containerTelescope))

					$this->containerTelescope = new ContainerTelescope;

				$container->setTelescope($this->containerTelescope);
			}
		}
	}
?>