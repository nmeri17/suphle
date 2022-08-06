<?php
	namespace Suphle\Contracts\Config;

	interface ModuleFiles extends ConfigMarker {

		/**
		 * @return Absolute path, with trailing slash
		*/
		public function getRootPath ():string;

		/**
		 * @return Absolute path, with trailing slash
		*/
		public function activeModulePath ():string;

		/**
		 * @return Absolute path, with trailing slash
		*/
		public function defaultViewPath():string;

		/**
		 * @return Absolute path, with trailing slash
		*/
		public function getImagePath ():string;

		/**
		 * @return Absolute path, with trailing slash
		*/
		public function componentsPath ():string;
	}
?>