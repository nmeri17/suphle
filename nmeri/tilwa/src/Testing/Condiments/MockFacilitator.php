<?php
	namespace Tilwa\Testing\Condiments;

	use PHPUnit\Framework\MockObject\MockBuilder;

	trait MockFacilitator {

		protected function positiveStub (string $target, array $overrides, array $constructorArguments = []):MockBuilder {

			$builder = $this->getBuilder($target, $constructorArguments, true);

			$this->stubSingle($overrides, $builder);

			return $builder;
		}

		protected function positiveStubMany (string $target, array $overrides, array $constructorArguments = []):MockBuilder {

			$builder = $this->getBuilder($target, $constructorArguments, true);

			$this->stubMany($overrides, $builder);

			return $builder;
		}

		/**
		 * Use when the other methods contain actions we don't wanna trigger
		*/
		protected function negativeStub (string $target, array $overrides, array $constructorArguments = []):MockBuilder {

			$builder = $this->getBuilder($target, $constructorArguments, false);

			$this->stubSingle($overrides, $builder);

			return $builder;
		}

		protected function negativeStubMany (string $target, array $overrides, array $constructorArguments = []):MockBuilder {

			$builder = $this->getBuilder($target, $constructorArguments, false);

			$this->stubMany($overrides, $builder);

			return $builder;
		}

		private function stubSingle (array $overrides, MockBuilder $builder):void {

            foreach ($overrides as $method => $newValue)

            	$builder->expects($this->any())

            	->method($method)->will($this->returnValue($newValue));
		}

		/**
		 * Allows for stubbing multiple calls to SUT and receiving different results each time
		*/
		private function stubMany (array $overrides, MockBuilder $builder):void {

            foreach ($overrides as $method => $newValue) {

            	$expectation = $builder->expects($this->any())

            	->method($method);

            	if (is_array($newValue))

            		$expectation->will($this->onConsecutiveCalls(...$newValue));

            	else $expectation->will($this->returnValue($newValue));
            }
		}

		private function getBuilder (string $target, array $constructorArguments, bool $retainOtherMethods):MockBuilder {

			$builder = $this->getMockBuilder($target);

			if (!empty($constructorArguments))

				$builder->setConstructorArgs($constructorArguments);

			if ($retainOtherMethods)

				$builder->setMethods(null);

			return $builder->getMock();
		}
	}
?>