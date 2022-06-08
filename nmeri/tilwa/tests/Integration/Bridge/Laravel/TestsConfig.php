<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Illuminate\Contracts\Config\Repository as RepositoryContract;

	class TestsConfig extends IsolatedComponentTest {

		use CommonBinds;

	    protected function getUnderlyingConfig ():RepositoryContract {

			return $this->container->getClass(LaravelContainer::class) // trigger config lifting

			->make("config");
	    }
	}
?>