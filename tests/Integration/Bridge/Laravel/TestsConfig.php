<?php
	namespace Suphle\Tests\Integration\Bridge\Laravel;

	use Suphle\Contracts\Bridge\LaravelContainer;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Illuminate\Contracts\Config\Repository as RepositoryContract;

	class TestsConfig extends IsolatedComponentTest {

		use CommonBinds;

	    protected function getUnderlyingConfig ():RepositoryContract {

			return $this->container->getClass(LaravelContainer::class) // trigger config lifting

			->make("config");
	    }
	}
?>