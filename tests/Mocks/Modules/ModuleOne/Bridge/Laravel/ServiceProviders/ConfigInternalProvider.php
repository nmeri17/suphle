<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders;

	use Illuminate\Support\ServiceProvider;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders\Exports\ConfigInternal;

	class ConfigInternalProvider extends ServiceProvider {

		public function register () {

			$this->app->singleton(ConfigInternal::class, function ($app) {

				return new ConfigInternal;
			});
		}
	}
?>