<?php
	namespace Tilwa\Tests\Unit\Hydration;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Auth\UserContract;

	use Tilwa\Testing\{TestTypes\TestVirginContainer, Proxies\Extensions\CheckProvisionedClasses};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{ ARequiresBCounter, BCounter};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\CInterface;

	use ReflectionMethod, Exception, stdClass;

	class BaseContainerTest extends TestVirginContainer {

		private $container, $aRequires = ARequiresBCounter::class;

		protected function setUp ():void {

			$this->container = new Container;
		}

		public function test_lastHydratedFor () {

			$this->assertNull($this->container->lastHydratedFor());
		}

		public function test_decorateProvidedConcrete_doesnt_overflow_memory () {

			$sut = $this->positiveDouble(Container::class, [], [

				"getProvidedConcrete" => [1, [$this->anything()]]
			]); // then

			$sut->decorateProvidedConcrete($this->aRequires); // when
		}

		public function test_can_provide_arguments () {

			$container = new CheckProvisionedClasses;

			$provision = [

				BCounter::class => new BCounter
			];

			$container->whenType($this->aRequires)->needsAny($provision);

			$this->assertTrue($container->matchesNeedsProvision($this->aRequires, $provision));
		}

		public function test_getClass_tries_returning_provided () {

			$stub = new stdClass;

			$sut = $this->positiveDouble(Container::class, [

				"decorateProvidedConcrete" => $stub // given
			]);

			$this->assertSame( // then
				$sut->getClass($this->aRequires), // when

				$stub
			);
		}

		public function test_unprovided_get_to_decorateProvidedConcrete_returns_null () {

			$this->assertNull($this->container->decorateProvidedConcrete($this->aRequires));
		}

		public function test_getClass_tries_to_instantiate_concrete () {

			// given
			$sut = $this->positiveDouble(Container::class, [], [

				"instantiateConcrete" => [ // then
					$this->atLeastOnce(), [
						$this->equalTo($this->aRequires)
					]
				]
			]);

			$this->bootContainer($sut);

			$sut->getClass($this->aRequires); // when
		}

		public function test_can_directly_instantiate_concrete_without_interface () {

			// given
			$sut = $this->withArgumentsForARequires([

				"getDecorator" => $this->stubDecorator()
			]);

			$this->bootContainer($sut);

			$this->assertInstanceOf( // then
				$this->aRequires,

				$sut->instantiateConcrete($this->aRequires) // when
			);
		}

		private function withArgumentsForARequires (array $otherOverrides = []) {

			return $this->positiveDouble(Container::class, array_merge([

				"getMethodParameters" => array_merge($this->manuallyStubify([BCounter::class]), [""])
			], $otherOverrides));
		}

		public function test_can_hydrate_concrete_for_caller () {

			// given
			$sut = $this->withArgumentsForARequires();

			$freshlyCreated = $sut->initializeHydratingForAction(
				$this->aRequires,

				function ($name) use ($sut) {

					return $sut->hydrateConcreteForCaller($name);
			}); // when

			// then
			$this->assertInstanceOf($this->aRequires, $freshlyCreated->getConcrete());

			$this->assertSame($freshlyCreated->getCreatedFor(), get_class());
		}

		public function test_can_hydrate_method_parameters_without_interface () {

			$sut = $this->positiveDouble(Container::class, [

				"lastHydratedFor" => $this->aRequires,

				"getDecorator" => $this->stubDecorator()
			]);

			$this->bootContainer($sut);

			// given
			$reflectedCallable = new ReflectionMethod($this->aRequires, "__construct");

			$provisionContext = $sut->getRecursionContext();

			$parameters = $sut->populateDependencies($reflectedCallable, $provisionContext); // when

			// then
			$this->assertTrue (is_string( $parameters["primitive"]));

			$this->assertInstanceOf (BCounter::class, $parameters["b1"]);
		}

		public function test_internal_get_parameters_calls_populateDependencies () {

			// given
			$sut = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator()
			], [
				
				"populateDependencies" => [1, [

					$this->anything(), $this->anything() // not null, since there's always a context when a class method is being populated (fallback to universal)
				]] // then
			]);

			$this->bootContainer($sut);

			$parameters = $sut->internalMethodGetParameters(function () use ($sut) {

				return $sut->getMethodParameters("__construct", $this->aRequires);
			});
		}

		private function manuallyStubify (array $types):array {

			return array_map(function ($type) {

				return $this->positiveDouble($type, []);
			}, $types);
		}

		public function test_request_for_interface_skip_bridge_calls_provideInterface () {

			$interfaceName = UserContract::class;

			$sut = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator()
			], [

				"provideInterface" => [1, [$interfaceName]], // then
			]);

			$this->bootContainer($sut);

			$sut->getClass($interfaceName);
		}

		public function test_hydrating_interface_without_bind_will_terminate () {

			$this->expectException(Exception::class); // then

			$sut = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator()
			]);

			$this->bootContainer($sut);

			$this->withDefaultInterfaceCollection($sut);

			$sut->getClass(CInterface::class); // when
		}
	}
?>