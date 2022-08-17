<?php
	namespace Suphle\Tests\Integration\Services\Proxies;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{LoadablesForsaken, LoadablesChosenOne, LoadableDependency};

	class OnlyLoadedByTest extends IsolatedComponentTest {

		use CommonBinds;

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