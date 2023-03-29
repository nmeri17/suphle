<?php
	namespace Suphle\Tests\Integration\Hydration;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{CircularConstructor1, MethodCircularContainer};

	class CircularDependenciesTest extends IsolatedComponentTest {

		protected function getContainerMocks ():array {

			return [ // is executed for all tests on this class

				"triggerCircularWarning" => [1, []] // then
			];
		}

		public function test_unmuted_circular_dependencies_raises_warning () {

			$this->container->getClass(CircularConstructor1::class); // when
		}

		/**
		 * This happens because $hydratingForStack stores both caller and target, in case it needs to return either
		*/
		public function test_circular_can_be_triggered_outside_ctor_explicit () {

			(new MethodCircularContainer($this->container))

			->loadFromContainer(); // when
		}

		public function test_circular_can_be_triggered_outside_ctor_implicit () {

			$this->container->getClass(MethodCircularContainer::class)

			->loadFromContainer(); // when
		}
	}
?>