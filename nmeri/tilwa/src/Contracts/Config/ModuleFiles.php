<?php
	namespace Tilwa\Contracts\Config;

	interface ModuleFiles extends ConfigMarker {

		/**
		 * @return Path with trailing slash
		*/
		public function getRootPath ():string;

		/**
		 * @return Path with trailing slash
		*/
		public function activeModulePath ():string;

		/**
		 * @return Absolute path
		*/
		public function getViewPath():string;

		/**
		 * @return Absolute path
		*/
		public function getImagePath ():string;
	}
?>