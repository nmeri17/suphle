<?php
	namespace Tilwa\Testing\Condiments;

	use ReflectionMethod;

	use PHPUnit\Framework\MockObject\{MockBuilder, Stub\Stub, Rule\InvocationOrder, Builder\InvocationMocker};

	trait MockFacilitator {

		/**
		 * @param {mockMethods} = [string method => [int|InvocationOrder numTimes, [arguments]]]
		*/
		protected function positiveDouble (string $target, array $stubs, array $mockMethods = [], array $constructorArguments = [])/*:MockBuilder*/ {

			$builder = $this->getBuilder(
				$target, $constructorArguments,

				$this->computeMethodsToRetain($stubs, $mockMethods)
			);

			$this->stubSingle($stubs, $builder);

			$this->mockCalls($mockMethods, $builder);

			return $builder;
		}

		protected function positiveDoubleMany (string $target, array $stubs, array $mockMethods = [], array $constructorArguments = [])/*:MockBuilder*/ {

			$builder = $this->getBuilder(
				$target, $constructorArguments,

				$this->computeMethodsToRetain($stubs, $mockMethods)
			);

			$this->stubMany($stubs, $builder);

			$this->mockCalls($mockMethods, $builder);

			return $builder;
		}

		protected function mockCalls (array $calls, $builder):void {

			foreach ($calls as $method => $behavior) {

				$this->getCallCount($builder, $behavior[0])

				->method($method)->with(...$behavior[1]);
			}
		}

		/**
		 * @param {count} int|InvocationOrder
		 * 
		 * @return InvocationMocker
		*/
		private function getCallCount ($builder, $count) {

			return $builder->expects(is_int($count) ? $this->exactly($count): $count);
		}

		/**
		 * Use when the other methods contain actions we don't wanna trigger
		*/
		protected function negativeDouble (string $target, array $stubs, array $mockMethods = [], array $constructorArguments = [])/*:MockBuilder*/ {

			$builder = $this->getBuilder(
				$target, $constructorArguments,

				$this->computeMethodsToRetain($stubs, $mockMethods),

				true
			);

			$this->stubSingle($stubs, $builder);

			$this->mockCalls($mockMethods, $builder);

			return $builder;
		}

		protected function stubSingle (array $stubs, /*MockBuilder*/ $builder):void {

			foreach ($stubs as $method => $newValue)

				$builder->expects($this->any())

				->method($method)->will($this->wrapStubBehavior($newValue));
		}

		/**
		 * Allows for stubbing multiple calls to SUT and receiving different results each time
		*/
		private function stubMany (array $stubs, /*MockBuilder*/ $builder):void {

			foreach ($stubs as $method => $newValue) {

				$expectation = $builder->expects($this->any())

				->method($method);

				if (is_array($newValue))

					$expectation->will($this->onConsecutiveCalls(...$newValue));

				else $expectation->will($this->wrapStubBehavior($newValue));
			}
		}

		private function wrapStubBehavior ($value):Stub {

			return $value instanceof Stub ? $value: $this->returnValue($value);
		}

		private function getBuilder (string $target, array $constructorArguments, array $methodsToRetain, bool $isNegative = false)/*:MockBuilder*/ {

			$builder = $this->getMockBuilder($target);

			if (!empty($constructorArguments))

				$builder->setConstructorArgs($constructorArguments);

			else $builder->disableOriginalConstructor();

			if (!$isNegative)

				$builder->onlyMethods($methodsToRetain);

			else $builder->onlyMethods(get_class_methods($target));

			/*$builder->disableProxyingToOriginalMethods()

			->disableAutoReturnValueGeneration()*/;

			$builder->disableArgumentCloning();

			return $builder->getMock();
		}

		protected function replaceConstructorArguments (string $target, array $constructorstubs, array $methodstubs)/*:MockBuilder*/ {

			$reflectedConstructor = new ReflectionMethod($target, "__construct");

			$arguments = $this->mockDummyUnion($reflectedConstructor->getParameters(), $constructorstubs);

			return $this->positiveDouble($target, $methodstubs, $arguments);
		}

		private function mockDummyUnion (array $parameters, array $replacements):array {

			return array_map(function ($parameter) use ($replacements) {

				$parameterName = $parameter->getName();

				if (array_key_exists($parameterName, $replacements))

					return $replacements[$parameterName];

				return $this->positiveDouble($parameter->getType()->getName(), []);
			}, $parameters);
		}

		private function computeMethodsToRetain (array $stubMethods, array $mockMethods):array {

			$mergedMethods = array_merge(array_keys($stubMethods), array_keys($mockMethods));

			return array_unique($mergedMethods);
		}

		protected function positiveAbstractClass (string $target, array $mockMethods = [], array $constructorArguments = []) {

			return $this->doubleAbstractClass($target, $mockMethods, $constructorArguments );
		}

		protected function negativeAbstractClass (string $target, array $mockMethods = [], array $constructorArguments = []) {

			return $this->doubleAbstractClass($target, $mockMethods, $constructorArguments, true);
		}

		private function doubleAbstractClass (string $target, array $mockMethods = [], array $constructorArguments = [], bool $isNegative = false) {

			return $this->getMockForAbstractClass(
				
				$target, $constructorArguments, "",

				/*callOriginalConstructor*/!empty($constructorArguments),
				/*callOriginalClone*/ !$isNegative,

				/*callAutoload*/ true,

				$mockMethods
			);
		}
	}
?>