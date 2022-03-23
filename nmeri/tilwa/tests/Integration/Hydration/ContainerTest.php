<?php
	namespace Tilwa\Tests\Integration\Hydration;

	use Tilwa\Hydration\{Container, Structures\NamespaceUnit};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Interfaces\CInterface, Config\ServicesMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{NeedsSpace, CircularConstructor1, CircularConstructor2, ARequiresBCounter, BCounter, CConcrete, V1\RewriteSpaceImpl};

	class ContainerTest extends IsolatedComponentTest {

		private $aRequires = ARequiresBCounter::class;

		public function test_providing_caller_gets_injected () {

			$container = $this->container;

			$ourB = new BCounter;

			$container->whenType($this->aRequires)->needsAny([

				BCounter::class => $ourB
			]);

			$aConcrete = $container->getClass($this->aRequires);

			$this->assertSame($aConcrete->getConstructorB(), $ourB);
		}

		public function test_any_caller_can_get_universal () {

			$container = $this->container;

			$ourB = new BCounter;

			$container->whenTypeAny()->needsAny([

				BCounter::class => $ourB
			]);

			$aConcrete = $container->getClass($this->aRequires);

			$this->assertSame($aConcrete->getConstructorB(), $ourB);

			$this->assertSame($aConcrete->getInternalB($container), $ourB);
		}

		public function test_provided_caller_needs_constructor () {

			$container = $this->container;

			$ourB = new BCounter;

			$ourB->setCount(5);

			$container->whenType($this->aRequires)
			->needs([

				BCounter::class => $ourB,
			]);

			$aConcrete = $container->getClass($this->aRequires);

			$this->assertGreaterThan(
				$aConcrete->getInternalB($container)->getCount(),

				$aConcrete->getConstructorB()->getCount()
			);
		}

		public function test_provided_method_gets_argument () {

			$container = $this->container;

			$ourB = new BCounter;

			$ourB->setCount(5);

			$aConcrete = $container->getClass($this->aRequires);

			$this->assertNotEquals($aConcrete->getConstructorB(), $ourB);

			$container->whenType($this->aRequires)->needsArguments([

				BCounter::class => $ourB
			]);

			$parameters = $container->getMethodParameters ( "receiveBCounter", $this->aRequires);

			$aConcrete->receiveBCounter(...array_values($parameters));

			$this->assertEquals($aConcrete->getConstructorB(), $ourB);
		}

		public function test_will_not_hydrate_interface_from_bound_concrete () {

			// given @see [simpleBinds] and [concreteBinds]

			$container = $this->container;

			$parameters = $container->getMethodParameters ( "receiveProvidedInterface", $this->aRequires); // when

			$aConcrete = $container->getClass($this->aRequires);

			$aConcrete->receiveProvidedInterface(...array_values($parameters));

			$this->assertNotEquals($aConcrete->getCInterface()->getValue(), 10); // then
		}

		protected function simpleBinds ():array {

			return [CInterface::class => CConcrete::class];
		}

		protected function concreteBinds ():array {

			return [CConcrete::class => new CConcrete(10)];
		}

		public function test_whenSpace() {

			$container = $this->container;

			$modulePath = "Tilwa\Tests\Mocks\Modules\ModuleOne\\";

			$mock = new RewriteSpaceImpl;

			// given
			$rewrite = new NamespaceUnit($modulePath . "Interfaces", $modulePath . "Concretes\V1", function (string $contract) {

				return $contract . "Impl";
			});

			$container->whenSpace($modulePath . "Concretes")

			->renameServiceSpace($rewrite);

			// when
			$result = $container->getClass(NeedsSpace::class)->getConcreteValue();

			// then
			$this->assertSame($result, $mock->getValue());
		}

		public function test_can_load_muted_circular_dependencies () {

			$container = $this->container;

			$count = 5;

			$container->whenType(CircularConstructor2::class)

			->needsArguments(compact("count"));

			$result = @$container->getClass(CircularConstructor1::class)->getDependencyValue(); // when

			$this->assertSame($result, $count); // then
		}

		public function test_unmuted_circular_dependencies_raises_warning () {

			$this->expectWarning(); // then

			$this->container->getClass(CircularConstructor1::class); // when
		}
	}
?>