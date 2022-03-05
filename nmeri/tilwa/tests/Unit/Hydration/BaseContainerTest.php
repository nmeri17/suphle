<?php
	namespace Tilwa\Tests\Unit\Hydration;

	use Tilwa\Hydration\Container;

	use PHPUnit\Framework\TestCase;

	use Prophecy\Argument;

	class BaseContainerTest extends TestCase {

		private $container;

		protected function setUp ():void {

			$this->container = new Container;
		}

		public function test_lastHydratedFor () {

			$this->assertNull($this->container->lastHydratedFor());
		}

		public function test_decorateProvidedConcrete_doesnt_overflow_memory () {

			$double = $this->prophesize(Container::class);

			$double->decorateProvidedConcrete(Argument::type("string"))

			->will(function ($args) {

				$this->lastHydratedFor()->willReturn(null); // given

				$this->getProvidedConcrete($args[0])->shouldBeCalled(); // then
			});

			$sut = $double->reveal();

			$sut->decorateProvidedConcrete("AwesomeClass"); // when
		}

		/*public function test_getRecursionContext () {

			// is null if nothing was provided
		}*/
	}
?>