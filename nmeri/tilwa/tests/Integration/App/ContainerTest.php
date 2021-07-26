<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\App\Container;

	use Tilwa\Testing\BaseTest;

	use Tilwa\Tests\Mocks\App\{ARequiresBCounter, BCounter};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Interfaces\CInterface;

	use Tilwa\Contracts\Config\Services as ServicesContract;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\ServicesMock;

	class ContainerTest extends BaseTest {

		private $aRequires = ARequiresBCounter::class;

		public function test_provided_caller_can_get () {

			$container = $this->container;

			$ourB = new BCounter;

			$container->whenType($this->aRequires)->needsAny([

				BCounter::class => $ourB
			]);

			$aConcrete = $container->getClass($this->aRequires);

			$this->assertSame($aConcrete->getConstructorB(), $ourB);

			$this->assertSame($aConcrete->getInternalB(), $ourB);
		}

		public function test_any_caller_can_get_universal () {

			$container = $this->container;

			$ourB = new BCounter;

			$container->whenTypeAny()->needsAny([

				BCounter::class => $ourB
			]);

			$aConcrete = $container->getClass($this->aRequires);

			$this->assertSame($aConcrete->getConstructorB(), $ourB);

			$this->assertSame($aConcrete->getInternalB(), $ourB);
		}

		public function test_provided_caller_needs_constructor () {

			$container = $this->container;

			$ourB = new BCounter;

			$ourB->setCount(5);

			$container->whenType($this->aRequires)
			->needs([

				BCounter::class => $ourB,
			]); // later test that string here won't work

			$aConcrete = $container->getClass($this->aRequires);

			$this->assertGreaterThan($aConcrete->getConstructorB()->getCount(), $aConcrete->getInternalB()->getCount());
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

		public function test_hydrate_interface_from_service_provider () {

			$container = $this->container;

			$container->setConfigs($this->getMockServiceConfig());

			$parameters = $container->getMethodParameters ( "receiveProvidedInterface", $this->aRequires);

			$aConcrete = $container->getClass($this->aRequires);

			$aConcrete->receiveProvidedInterface(...array_values($parameters));

			$this->assertEquals($aConcrete->getCInterface()->getValue(), 10);
		}

		private function getMockServiceConfig ():array {

			$oldConfigs = $this->containerConfigs();

			$oldConfigs[ServicesContract::class] = ServicesMock::class;

			return $oldConfigs;
		}
	}
?>