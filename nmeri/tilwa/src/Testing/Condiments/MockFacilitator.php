<?php
	namespace Tilwa\Testing\Condiments;

	use Mockery\{MockInterface, Adapter\Phpunit\MockeryPHPUnitIntegration};

	trait MockFacilitator {

		use MockeryPHPUnitIntegration;

		protected function positiveStub (string $target, array $overrides, array $constructorArguments = []):MockInterface {

			$builder = $this->getBuilder($target, $constructorArguments, true);

			$this->stubSingle($overrides, $builder);

			return $builder;
		}

		protected function positiveStubMany (string $target, array $overrides, array $constructorArguments = []):MockInterface {

			$builder = $this->getBuilder($target, $constructorArguments, true);

			$this->stubMany($overrides, $builder);

			return $builder;
		}

		/**
		 * Use when the other methods contain actions we don't wanna trigger
		*/
		protected function negativeStub (string $target, array $overrides, array $constructorArguments = []):MockInterface {

			$builder = $this->getBuilder($target, $constructorArguments, false);

			$this->stubSingle($overrides, $builder);

			return $builder;
		}

		protected function negativeStubMany (string $target, array $overrides, array $constructorArguments = []):MockInterface {

			$builder = $this->getBuilder($target, $constructorArguments, false);

			$this->stubMany($overrides, $builder);

			return $builder;
		}

		private function stubSingle (array $overrides, MockInterface $builder):void {

            foreach ($overrides as $method => $newValue)

            	$builder->shouldReceive($method)->andReturn($newValue);
		}

		/**
		 * Allows for stubbing multiple calls to SUT and receiving different results each time
		*/
		private function stubMany (array $overrides, MockInterface $builder):void {

            foreach ($overrides as $method => $newValue) {

            	$expectation = $builder->shouldReceive($method);

            	if (is_array($newValue))

            		$expectation->willReturn(...$newValue);

            	else $expectation->andReturn($newValue);
            }
		}

		private function getBuilder (string $target, array $constructorArguments, bool $retainOtherMethods):MockInterface {

			$builder = Mockery::mock($target, $constructorArguments);

			if ($retainOtherMethods)

				$builder = $builder->makePartial();

			return $builder;
		}
	}
?>