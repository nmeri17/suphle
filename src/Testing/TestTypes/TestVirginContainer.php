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
		 * @param {callables} Expects them all to be methods, not closures or anonymous methods
		*/
		protected function dataProvider (array $callables, callable $testBody):void {

			foreach ($callables as $provider) {

				foreach ($provider() as $index => $dataFixture)

					try { $testBody(...$dataFixture); }

					catch (Throwable $exception) { // test failures throw ExpectationFailedException, but without catching errors, error message will appear as if all providers and data sets failed

						echo $this->providerExceptionMessage($provider, $index, $dataFixture);

						throw $exception;
					}
			}
		}

		private function providerExceptionMessage (array $providerCallable, int $errorIndex, array $dataRow):string {

			$newLine = "\n";

			$providerName = get_class($providerCallable[0]) . "::". $providerCallable[1];

			$messages = [
				"$providerName with data set #$errorIndex:",

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