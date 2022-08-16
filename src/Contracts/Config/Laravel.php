<?php
	namespace Suphle\Contracts\Config;

	interface Laravel extends ConfigMarker {

		/**
		 * [configName => My\Suphle\Config::class]
		*/
		public function configBridge ():array;

		/**
		 * [concrete::class => provider]
		*/
		public function getProviders ():array;

		public function registersRoutes ():bool;

		public function usesPackages ():bool;

		/**
		 * relative path from module folder
		*/
		public function frameworkDirectory ():string;
	}
?>