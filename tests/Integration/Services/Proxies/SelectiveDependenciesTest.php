<?php
	namespace Suphle\Tests\Integration\Services\Proxies;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\Selective\{BlankUpdatelessController, RandomConcreteController, ForbiddenDependencyController};

	class SelectiveDependenciesTest extends IsolatedComponentTest {

		use CommonBinds;

		protected $usesRealDecorator = true;

		public function test_rejected_type_throws_errors () {

			$this->expectException(UnacceptableDependency::class); // then

			$this->container->getClass(ForbiddenDependencyController::class); // when
		}

		public function test_unknown_type_throws_errors () {

			$this->expectException(UnacceptableDependency::class); // then

			$this->container->getClass(RandomConcreteController::class); // when
		}

		public function test_approved_type_is_freely_injected () {

			$this->assertNotNull( // then
				
				$this->container->getClass(BlankUpdatelessController::class) // when
			);
		}
	}
?>