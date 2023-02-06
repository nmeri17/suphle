<?php
	namespace Suphle\Tests\Integration\Bridge\Laravel;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Bridge\LaravelContainer;

	use Suphle\Testing\TestTypes\ModuleLevelTest;

	use Illuminate\Contracts\Config\Repository as RepositoryContract;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class TestsConfig extends ModuleLevelTest {

		protected function getModules ():array {

			return [
				new ModuleOneDescriptor (new Container)
			];
		}

	    protected function getUnderlyingConfig ():RepositoryContract {

			return $this->getContainer()->getClass(LaravelContainer::class) // trigger config lifting

			->make("config");
	    }
	}
?>