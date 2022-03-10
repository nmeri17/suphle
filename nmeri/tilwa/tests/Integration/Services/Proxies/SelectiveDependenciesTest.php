<?php
	namespace Tilwa\Tests\Integration\Services\Proxies;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares\AttemptsLoadingUser;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\Selective\{BlankUpdatelessController, RandomConcreteController};

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class SelectiveDependenciesTest extends IsolatedComponentTest {

		public function test_rejected_type_throws_errors () {

			$this->expectException(UnacceptableDependency::class); // then

			$this->container->getClass(AttemptsLoadingUser::class); // when
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