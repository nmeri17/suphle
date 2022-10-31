<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ServiceProviders;

	use Illuminate\Support\ServiceProvider;

	use Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ServiceProviders\Exports\ConfigConstructor;

	class RegistersRouteProvider extends ServiceProvider {

		public function register () {

			$this->app->singleton(ConfigConstructor::class, fn($app) => new ConfigConstructor(config("nested.first_level")));
		}

		public function boot () {

			$this->loadRoutesFrom(__DIR__ . "/../routes/web.php");
		}
	}
?>