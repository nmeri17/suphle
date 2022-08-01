<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Config;

	use Suphle\Config\Laravel as ParentConfig;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks\{AppConfig, NestedConfig};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders\{RegistersRouteProvider, ConfigInternalProvider};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders\Exports\{ConfigInternal, ConfigConstructor};

	class LaravelMock extends ParentConfig {

		/**
		 * {@inheritdoc}
		*/
		public function configBridge ():array {

			return [

				"app" => AppConfig::class,

				"nested" => NestedConfig::class
			];
		}

		/**
		 * {@inheritdoc}
		*/
		public function getProviders ():array {

			return [

				ConfigConstructor::class => RegistersRouteProvider::class,

				ConfigInternal::class => ConfigInternalProvider::class
			];
		}

		/**
		 * {@inheritdoc}
		*/
		public function registersRoutes ():array {

			return [

				RegistersRouteProvider::class
			];
		} 

		/**
		 * {@inheritdoc}
		*/
		public function usesPackages ():bool {

			return false;
		}
	}
?>