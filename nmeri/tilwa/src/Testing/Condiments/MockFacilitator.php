<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Hydration\{Container, Structures\ObjectDetails};

	use PHPUnit\Framework\MockObject\{ MockObject, Stub\Stub, Rule\InvocationOrder, Builder\InvocationMocker};

	use ReflectionMethod, ReflectionClass, Exception;

	trait MockFacilitator {

		/**
		 * @param {mockMethods} = [string method => [int|InvocationOrder numTimes, [arguments]]]
		 * 
		 * @return Mock version of given [target], with an auto-generated class name
		*/
		protected function positiveDouble (string $target, array $stubs = [], array $mockMethods = [], ?array $constructorArguments = null):MockObject {

			$builder = $this->getBuilder(
				$target, $constructorArguments,

				$this->computeMethodsToRetain($stubs, $mockMethods)
			);

			$this->stubSingle($stubs, $builder);

			$this->mockCalls($mockMethods, $builder);

			return $builder;
		}

		protected function positiveDoubleMany (string $target, array $stubs = [], array $mockMethods = [], ?array $constructorArguments = null) {

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
		 * Stubs all methods out except those explicitly provided
		*/
		protected function negativeDouble (string $target, array $stubs = [], array $mockMethods = [], ?array $constructorArguments = null) {

			$allMethods = get_class_methods($target);

			$builder = $this->getBuilder( $target, $constructorArguments, $allMethods);

			$this->stubSingle($stubs, $builder);

			$this->mockCalls($mockMethods, $builder);

			return $builder;
		}

		protected function stubSingle (array $stubs, MockObject $builder):void {

			foreach ($stubs as $method => $newValue)

				$builder->expects($this->any())

				->method($method)->will($this->wrapStubBehavior($newValue));
		}

		/**
		 * Allows for stubbing multiple calls to SUT and receiving different results each time
		*/
		private function stubMany (array $stubs, MockObject $builder):void {

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

		/**
		 * @param {constructorArguments} when null, constructor will be skipped
		 * @return MockObject version of given [target]
		*/
		private function getBuilder (string $target, ?array $constructorArguments, array $methodsToRetain):MockObject {

			$builder = $this->getMockBuilder($target);

			if (!is_null($constructorArguments))

				$builder->setConstructorArgs($constructorArguments);

			else $builder->disableOriginalConstructor();

			$builder->onlyMethods($methodsToRetain);

			$builder->disableArgumentCloning();

			if ((new ReflectionClass($target))->isAbstract())

				return $builder->getMockForAbstractClass();

			return $builder->getMock();
		}

		/**
		 * @param {invokeConstructor} Constructors can't invoke stubbed methods during the doubling process since they're unavailable then. Constructors with this requirement have to be triggered manually
		 * When it receives argument names not matching method signature, it doesn't complain but returns the double equivalent
		*/
		protected function replaceConstructorArguments (

			string $target, array $constructorStubs,

			array $methodStubs = [], array $mockMethods = [],

			bool $positiveDouble = true, bool $positiveConstructor = true,

			bool $useBaseContainer = true, bool $invokeConstructor = false
		):MockObject {

			$this->ensureAssocConstructor($constructorStubs);

			$reflectedConstructor = new ReflectionMethod($target, Container::CLASS_CONSTRUCTOR);

			$arguments = $this->mockDummyUnion(

				$reflectedConstructor->getParameters(),

				$constructorStubs, $positiveConstructor,

				$useBaseContainer
			);

			$doubleMode = $positiveDouble ? "positiveDouble": "negativeDouble";

			if (!$invokeConstructor)

				$double = $this->$doubleMode($target, $methodStubs, $mockMethods, $arguments);

			else {

				$double = $this->$doubleMode($target, $methodStubs, $mockMethods);

				$double->__construct(...array_values($arguments));
			}

			return $double;
		}

		private function ensureAssocConstructor (array $constructorStubs):void {

			array_walk($constructorStubs, function ($value, $key) {

				if (is_numeric($key))

					throw new Exception("Stub array must be associative");
			});
		}

		private function mockDummyUnion (array $parameters, array $replacements, bool $isPositive, bool $useBaseContainer):array {

			return array_map(function ($parameter) use ($replacements, $isPositive, $useBaseContainer) {

				$parameterName = $parameter->getName();

				$container = $this->getContainer();

				if (array_key_exists($parameterName, $replacements))

					return $replacements[$parameterName];

				$parameterType = $parameter->getType();

				$argumentType = $parameterType->getName();

				if ($argumentType == Container::class && $useBaseContainer)

					return $container;

				if ($parameterType->isBuiltin())

					return $container->getClass(ObjectDetails::class)

					->getScalarValue($argumentType);

				return $isPositive?
					$this->positiveDouble($argumentType):

					$this->negativeDouble($argumentType);
			}, $parameters);
		}

		private function computeMethodsToRetain (array $stubMethods, array $mockMethods):array {

			$mergedMethods = array_merge(array_keys($stubMethods), array_keys($mockMethods));

			return array_unique($mergedMethods);
		}
	}
?>