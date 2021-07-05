<?php
	namespace Tilwa\Tests\Unit\App;

	use Tilwa\App\Container;

	use Tilwa\Testing\BaseTest;

	use Prophecy\Argument;

	use stdClass;

	use AspectMock\Test as AspectTest;

	use Tilwa\Tests\StronglyTyped\App\{ARequiresBCounter, BCounter};

	class ContainerTest extends BaseTest {

		/*public function test_provided_caller_can_get () {

			$producer = $this->getMockBuilder(stdClass::class)->getMock();

			$consumer = $this->getMockBuilder(stdClass::class)->addMethods(['doConsume'])->getMock();

			$consumerName = get_class($consumer);

			$producerName = get_class($producer);

			$this->container->whenType($consumerName)->needsAny([

				$producerName => $producer
			]);

			$this->overrideNativeMethod($consumerName);

			$consumer->expects($this->any())
			->method('doConsume')

			->will($this->returnCallback(function($container, $dependency) {

				return $container->getClassForContext($dependency);
			}));

			$result = $consumer->doConsume($this->container, $producerName);

			$this->assertEquals($result, $producer);
		}

		public function test_any_caller_can_get_universal () {

			$producer = $this->getMockBuilder(stdClass::class)->getMock();

			$consumer = $this->getMockBuilder(stdClass::class)->addMethods(['doConsume'])->getMock();

			$consumerName = get_class($consumer);

			$producerName = get_class($producer);

			$this->container->whenTypeAny()->needsAny([

				$producerName => $producer
			]);

			$this->overrideNativeMethod($consumerName);

			$consumer->expects($this->any())
			->method('doConsume')

			->will($this->returnCallback(function($container, $dependency) {

				return $container->getClassForContext($dependency);
			}));

			$result = $consumer->doConsume($this->container, $producerName);

			$this->assertEquals($result, $producer);
		}

		public function test_nonprovided_gets_nothing () {

			$producer = $this->getMockBuilder(stdClass::class)->getMock();

			$consumer = $this->getMockBuilder(stdClass::class)->addMethods(['doConsume'])->getMock();

			$consumerName = get_class($consumer);

			$producerName = get_class($producer);

			$this->overrideNativeMethod($consumerName);

			$consumer->expects($this->any())
			->method('doConsume')

			->will($this->returnCallback(function($container, $dependency) {

				return $container->getClassForContext($dependency);
			}));

			$result = $consumer->doConsume($this->container, $producerName);

			$this->assertNull($result);
		}*/

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

		/*public function test_provided_caller_needs_internals () {
			$this->markTestIncomplete(
	            'This test has not been implemented yet.'
	        );
		}

		public function test_provided_caller_needsAny () {
			$this->markTestIncomplete(
	            'This test has not been implemented yet.'
	        );
		}

		public function test_dependency_gets_provision () {
			$this->markTestIncomplete(
	            'This test has not been implemented yet.'
	        );
		}*/

		private function overrideNativeMethod ($consumerName) {

			AspectTest::func("Tilwa\\App", "debug_backtrace", [

				["class" => Container::class],

				["class" => $consumerName]
			]);
		}
	}
?>