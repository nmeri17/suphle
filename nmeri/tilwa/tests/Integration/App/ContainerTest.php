<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\App\Container;

	use Tilwa\Testing\BaseTest;

	use Tilwa\Tests\Mocks\App\{ARequiresBCounter, BCounter};

	class ContainerTest extends BaseTest {

		public function test_provided_caller_can_get () {

			$ourB = new BCounter;

			$this->container->whenType(ARequiresBCounter::class)->needsAny([

				BCounter::class => $ourB,

				Container::class => $this->container
			]);

			$a = $this->container->getClass(ARequiresBCounter::class);

			$this->assertSame($a->getConstructorB(), $ourB);

			$this->assertSame($a->getInternalB(), $ourB);
		}

		public function test_any_caller_can_get_universal () {

			$ourB = new BCounter;

			$this->container->whenTypeAny()->needsAny([

				BCounter::class => $ourB,

				Container::class => $this->container
			]);

			$a = $this->container->getClass(ARequiresBCounter::class);

			$this->assertSame($a->getConstructorB(), $ourB);

			$this->assertSame($a->getInternalB(), $ourB);
		}

		public function test_provided_caller_needs_constructor () {

			$ourB = new BCounter;

			$ourB->setCount(5);

			$this->container->whenType(ARequiresBCounter::class)
			->needsArguments([

				Container::class => $this->container
			])
			->needs([

				BCounter::class => $ourB,
			]); // later test that string here won't work

			$a = $this->container->getClass(ARequiresBCounter::class);

			$this->assertGreaterThan($a->getConstructorB()->getCount(), $a->getInternalB()->getCount());
		}
	}
?>