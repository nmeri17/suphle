<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Laravel as LaravelConfig;

	class Laravel implements LaravelConfig {

		/**
		 * {@inheritdoc}
		*/
		public function configBridge ():array {

			return [];
		}

		/**
		 * {@inheritdoc}
		*/
		public function getProviders ():array {

			return [];
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