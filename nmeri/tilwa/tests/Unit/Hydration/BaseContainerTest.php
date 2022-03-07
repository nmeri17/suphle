<?php
	namespace Tilwa\Tests\Unit\Hydration;

	use Tilwa\Hydration\{Container, DecoratorHydrator};

	use Tilwa\Testing\Condiments\MockFacilitator;

	use Tilwa\Tests\Unit\Hydration\Extensions\CheckProvisionedClasses;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{ ARequiresBCounter, BCounter};

	use PHPUnit\Framework\TestCase;

	use Prophecy\Argument;

	use stdClass;

	class BaseContainerTest extends TestCase {

		use MockFacilitator;

		private $container, $aRequires = ARequiresBCounter::class;

		protected function setUp ():void {

			$this->container = new Container;
		}

		public function test_lastHydratedFor () {

			$this->assertNull($this->container->lastHydratedFor());
		}

		public function test_decorateProvidedConcrete_doesnt_overflow_memory () {

			$sut = $this->positiveStub(Container::class, [

				//"lastHydratedFor" => null // given
			], [], ["getProvidedConcrete"]);

			$sut->expects($this->once())->method("getProvidedConcrete")

			->with($this->anything()); // then

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

			$sut = $this->positiveStub(Container::class, [

				"decorateProvidedConcrete" => $stub // given
			]);

			$this->assertSame(
				$sut->getClass($this->aRequires), // when

				$stub
			); // then
		}

		public function test_unprovided_get_to_decorateProvidedConcrete_returns_null () {

			$this->assertNull($this->container->decorateProvidedConcrete($this->aRequires));
		}

		public function test_tries_to_instantiate_concretes () {

			// given
			$sut = $this->positiveStub(Container::class, [

				"loadLaravelLibrary" => null
			], [], ["instantiateConcrete"]);

			$this->stubInstantiateConcrete($sut); // then

			$this->bootContainer($sut);

			$sut->getClass($this->aRequires); // when
		}

		public function test_can_directly_instantiate_concrete_without_interface () {

			// given
			$sut = new class extends Container {

				protected function loadLaravelLibrary (string $fullName) {}

				public function setDecorator ($decorator):void {

					$this->decorator = $decorator;
				}
			};

			$sut->setDecorator ($this->positiveStub(DecoratorHydrator::class, [

				"scopeInjecting" => $this->returnArgument(0)
			]));

			$sut->initializeUniversalProvision();

			$this->assertInstanceOf( // then
				$this->aRequires,

				$sut->instantiateConcrete($this->aRequires) // when
			);
		}

		public function test_can_hydrate_concrete_for_caller () {

			$sut = $this->positiveStub(Container::class, [

				"getMethodParameters" => $this->manuallyStubify([BCounter::class, Container::class] + [""])
			]);

			$concrete = $sut->hydratingForAction(
				$this->aRequires, // given

				function ($name) use ($sut) {

					return $sut->hydrateConcreteForCaller($name);
			}); // when

			$this->assertInstanceOf($this->aRequires, $concrete); // then
		}

		private function manuallyStubify (array $types):array {

			return array_map(function ($type) {

				return $this->positiveStub($type, []);
			}, $types);
		}

		private function bootContainer ($container):void {

			$container->initializeUniversalProvision();

			$container->interiorDecorate();
		}

		private function stubInstantiateConcrete ($container):void {

			$container->expects($this->atLeastOnce())->method("instantiateConcrete")

			->will(
				$this->returnCallback(function ($subject) {

					return $this->positiveStub($subject, []);
				})
			);
		}
	}
?>