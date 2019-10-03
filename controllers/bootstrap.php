<?php

	namespace Controllers;

	use Nmeri\Tilwa\Route\RouteRegister;

	use Nmeri\Tilwa\Controllers\Bootstrap as InitApp;
	
	
	class Bootstrap extends InitApp {

		protected function setStaticVars ( $vars ) {

			$slash = DIRECTORY_SEPARATOR; $rootPath = dirname(__DIR__, 1) . $slash; // up one folder

			$this->container = [

				'rootPath' => $rootPath, 'router' => new RouteRegister, 'classes' => [],

				'routesDirectory' => 'routes', 'middlewareDirectory' => 'Middleware',

				'requestName' => $vars['path'], 'viewPath' => $rootPath . 'views'. $slash
			];
		}
	}

?>