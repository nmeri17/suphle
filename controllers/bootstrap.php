<?php

	namespace Controllers;

	use Tilwa\Route\RouteRegister;

	use Tilwa\Controllers\Bootstrap as InitApp;
	
	
	class Bootstrap extends InitApp {

		protected function setStaticVars ( $vars ) {

			$slash = DIRECTORY_SEPARATOR; $rootPath = dirname(__DIR__, 1) . $slash; // up one folder

			$this->container = [

				'router' => new RouteRegister, 'classes' => [], 'sourceNamespace' => 'Source\\',

				'routesDirectory' => 'routes', 'middlewareDirectory' => 'Middleware',

				'requestSlug' => $vars['path'], 'viewPath' => $rootPath . 'views'. $slash,

				'site_name' => $_SERVER['SERVER_NAME'], 'this_year' => date('Y')

			] + compact('rootPath', 'slash');
		}
	}

?>