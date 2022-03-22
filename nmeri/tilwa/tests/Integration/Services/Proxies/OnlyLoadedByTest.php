<?php
	namespace Tilwa\Tests\Integration\Services\Proxies;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{LoadablesForsaken, LoadablesChosenOne, LoadableDependency};

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class OnlyLoadedByTest extends IsolatedComponentTest {

		public function test_unwanted_dependency_throws_errors () {

			$this->expectException(UnacceptableDependency::class); // then

			$this->container->getClass(LoadablesForsaken::class); // when
		}

		public function test_permitted_dependency_is_injected () {

			$sut = $this->container->getClass(LoadablesChosenOne::class); // when

			$this->assertInstanceOf(LoadableDependency::class, $sut->getLoadable()); // then
		}
	}
?>