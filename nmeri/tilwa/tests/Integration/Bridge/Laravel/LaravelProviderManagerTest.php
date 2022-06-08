<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders\Exports\{ConfigConstructor, ConfigInternal};

	/**
	 * @see [ProvidedServiceWrapper]
	*/
	class LaravelProviderManagerTest extends TestsConfig {

		public function test_can_invoke_helpers_only_in_wrapped () {

			$this->markTestIncomplete();

			$laravelContainer = $this->container->getClass(LaravelContainer::class);

			// do random laravel op
			$sut = $laravelContainer->make(ConfigConstructor::class); // when

			$realSecondLevel = $this->getUnderlyingConfig()->get("nested.first_level.second_level");

			$this->assertSame($sut->getSecondLevel(), $realSecondLevel); // then
			
			// note: this is still a thing
			$this->assertFalse(function_exists("config")); // helper didn't leak out
		}
	}
?>