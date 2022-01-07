<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Contracts\Bridge\LaravelApp;

	use Illuminate\Contracts\Config\Repository as RepositoryContract;

	class TestsConfig extends IsolatedComponentTest {

	    protected function getUnderlyingConfig ():RepositoryContract {

			return $this->container->getClass(LaravelApp::class) // trigger config lifting

			->make("config");
	    }
	}
?>