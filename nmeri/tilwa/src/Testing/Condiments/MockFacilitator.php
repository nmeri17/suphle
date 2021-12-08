<?php
	namespace Tilwa\Testing\Condiments;

	use ReflectionMethod;

	use PHPUnit\Framework\MockObject\MockBuilder;

	trait MockFacilitator {

		protected function positiveStub (string $target, array $overrides, array $constructorArguments = []):MockBuilder {

			$builder = $this->getBuilder($target, $constructorArguments, $overrides);

			$this->stubSingle($overrides, $builder);

			return $builder;
		}

		protected function positiveStubMany (string $target, array $overrides, array $constructorArguments = []):MockBuilder {

			$builder = $this->getBuilder($target, $constructorArguments, $overrides);

			$this->stubMany($overrides, $builder);

			return $builder;
		}

		/**
		 * Use when the other methods contain actions we don't wanna trigger
		*/
		protected function negativeStub (string $target, array $overrides, array $constructorArguments = []):MockBuilder {

			$builder = $this->getBuilder($target, $constructorArguments, []);

			$this->stubSingle($overrides, $builder);

			return $builder;
		}

		protected function negativeStubMany (string $target, array $overrides, array $constructorArguments = []):MockBuilder {

			$builder = $this->getBuilder($target, $constructorArguments, []);

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

		private function getBuilder (string $target, array $constructorArguments, array $methodsToRetain):MockBuilder {

			$builder = $this->getMockBuilder($target);

			if (!empty($constructorArguments))

				$builder->setConstructorArgs($constructorArguments);

			if (!empty($methodsToRetain))

				$builder->setMethods(array_keys($methodsToRetain));

			return $builder->getMock();
		}

		protected function replaceConstructorArguments (string $target, array $constructorOverrides, array $methodOverrides):MockBuilder {

			$reflectedConstructor = new ReflectionMethod($target, "__construct");

			$arguments = $this->mockDummyUnion($reflectedConstructor->getParameters(), $constructorOverrides);

			return $this->positiveStub($target, $methodOverrides, $arguments);
		}

		private function mockDummyUnion (array $parameters, array $replacements):array {

			return array_map(function ($parameter) use ($replacements) {

				$parameterName = $parameter->getName();

				if (array_key_exists($parameterName, $replacements))

					return $replacements[$parameterName];

				return $this->positiveStub($parameter->getType()->getName(), []);
			}, $parameters);
		}
	}
?>