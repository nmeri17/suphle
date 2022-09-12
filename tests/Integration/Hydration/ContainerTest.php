<?php
	namespace Suphle\Tests\Integration\Hydration;

	use Suphle\Hydration\{Container, Structures\NamespaceUnit};

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Interfaces\CInterface, Config\ServicesMock};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{NeedsSpace, CircularConstructor1, CircularConstructor2, ARequiresBCounter, BCounter, CConcrete, MethodCircularContainer, V1\RewriteSpaceImpl};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\SubServiceLocation\{HydratorConsumer, UnknownUserLandHydrator};

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

			// lesser on the LHS
			$this->assertGreaterThan(
				$aConcrete->getConstructorB()->getCount(),

				$aConcrete->getInternalB($container)->getCount()
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

			$modulePath = "Suphle\Tests\Mocks\Modules\ModuleOne\\";

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

		public function test_sub_can_see_parent_provision () {

			$value = 10;

			$this->assertSame(

				$value, $this->provideParentCounter($value)

				->getParentsBCounter()->getCount()
			);
		}

		protected function provideParentCounter (int $counterValue):HydratorConsumer {

			$clientInstance = new BCounter;

			$clientInstance->setCount($counterValue);

			return $this->container->whenType(HydratorConsumer::class)

			->needs([BCounter::class => $clientInstance])

			->getClass(UnknownUserLandHydrator::class);
		}

		public function test_sub_has_no_personal_provision () { // inverse to confirm test_sub_can_see_parent_provision doesn't work without that parameter

			$value = 10;

			$this->assertNotSame(

				$value, $this->provideParentCounter($value)
			
				->getSelfBCounter()->getCount()
			);
		}
	}
?>