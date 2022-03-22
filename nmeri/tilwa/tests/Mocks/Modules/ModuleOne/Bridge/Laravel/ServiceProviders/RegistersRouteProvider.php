<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders;

	use Illuminate\Support\ServiceProvider;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders\Exports\ConfigConstructor;

	class RegistersRouteProvider extends ServiceProvider {

		public function register () {

			$this->app->singleton(ConfigConstructor::class, function ($app) {

				return new ConfigConstructor(config("nested.first_level"));
			});
		}

		public function boot () {

			$this->loadRoutesFrom(__DIR__ . "/../routes/web.php");
		}
	}
?>