<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Config\Laravel as ParentConfig;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks\{AppConfig, NestedConfig};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ServiceProviders\RegistersRouteProvider;

	class Laravel extends ParentConfig {

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

				RegistersRouteProvider::class
			];
		}

		/**
		 * {@inheritdoc}
		*/
		public function hasRoutes():bool {

			return true;
		} 

		/**
		 * {@inheritdoc}
		*/
		public function usesPackages ():bool {

			return false;
		}
	}
?>