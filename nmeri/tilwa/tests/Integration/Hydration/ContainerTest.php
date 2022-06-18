<?php
	namespace Tilwa\Tests\Integration\Hydration;

	use Tilwa\Hydration\{Container, Structures\NamespaceUnit};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Interfaces\CInterface, Config\ServicesMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{NeedsSpace, CircularConstructor1, CircularConstructor2, ARequiresBCounter, BCounter, CConcrete, MethodCircularContainer, V1\RewriteSpaceImpl};

	class ContainerTest extends IsolatedComponentTest {

		use CommonBinds {

			CommonBinds::simpleBinds as commonSimples;

			CommonBinds::concreteBinds as commonConcretes;
		}

		private $aRequires = ARequiresBCounter::class;

		protected function simpleBinds ():array {

			return array_merge($this->commonSimples(), [

				CInterface::class => CConcrete::class
			]);
		}

		protected function concreteBinds ():array {

			return array_merge($this->commonConcretes(), [

				CConcrete::class => new CConcrete(10)
			]);
		}

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

		public function test_needs_doesnt_use_provisions_from_needsArgument () {

			$container = $this->container;

			$ourB = new BCounter;

			$ourB->setCount(5);

			$container->whenType($this->aRequires)->needs([

				BCounter::class => $ourB
			]);

			$aConcrete = $container->getClass($this->aRequires);

			$this->assertGreaterThan(
				$aConcrete->getInternalB($container)->getCount(),

				$aConcrete->getConstructorB()->getCount()
			);
		}

		public function test_methods_can_read_needsArgument () {

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

		public function test_can_provide_internal_class_in_circular_dependency () {

			$container = $this->container;

			$count = 5;

			$container->whenType(CircularConstructor2::class)

			->needsArguments(compact("count"));

			$result = @$container->getClass(CircularConstructor1::class)->getDependencyValue(); // when

			$this->assertSame($count, $result); // then
		}

		public function test_unmuted_circular_dependencies_raises_warning () {

			$this->expectWarning(); // then

			$this->container->getClass(CircularConstructor1::class); // when
		}

		/**
		 * This happens because $hydratingForStack stores both caller and target, in case it needs to return either
		*/
		public function test_circular_can_be_triggered_outside_ctor_explicit () {

			$this->expectWarning(); // then

			(new MethodCircularContainer($this->container))

			->loadFromContainer(); // when
		}

		public function test_circular_can_be_triggered_outside_ctor_implicit () {

			$this->expectWarning(); // then

			$this->container->getClass(MethodCircularContainer::class)

			->loadFromContainer(); // when
		}
	}
?>