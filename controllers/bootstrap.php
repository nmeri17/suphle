<?php

	namespace Controllers;

	use Tilwa\Route\RouteRegister;

	use Tilwa\Controllers\Bootstrap as InitApp;

	use Doctrine\ORM\Tools\Setup;

	use Doctrine\ORM\EntityManager;

	use Models\User;
	
	
	class Bootstrap extends InitApp {

		// the objective is to plant your desired orm connections on $this->container['connection']
		protected function setConnection () {

			try {

				$connectionParams = [
					'dbname' => getenv('DBNAME'),

				    'user' => getenv('DBUSER'),

				    'password' => getenv('DBPASS'),

				    'driver' => 'pdo_mysql',
				];

				$paths = ["models"];

				$isDevMode = true;

				// custom edits
				$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

				$entityManager = EntityManager::create($connectionParams, $config);

				$this->container['connection'] = $entityManager;

				return $this;
			}
			catch (PDOException $e) {

				var_dump("unable to connect to mysql server", $e->getMessage());

				exit();
			}
		}

		protected function setStaticVars ( $vars ) {

			$slash = DIRECTORY_SEPARATOR;

			$rootPath = dirname(__DIR__, 1) . $slash; // up one folder

			$this->container = [

				'router' => new RouteRegister, 'classes' => [],

				'sourceNamespace' => 'Sources',

				'routesDirectory' => 'routes',

				'middlewareDirectory' => 'Middleware',

				'viewPath' => $rootPath . 'views'. $slash,

				'siteName' => @$_SERVER['SERVER_NAME'] ?? getenv('SITENAME'), // server name will be empty when running from cli

			] + compact('rootPath', 'slash');

			return $this;
		}

		protected function foundUser ( $session, string $apiToken = null) {
			$user = null; // guest

			if ($userId = @$session['tilwa_user_id'])

				$user = $this->connection

				->getRepository(User::class)

				->find($userId);

			return $user;
		}
	}

?>