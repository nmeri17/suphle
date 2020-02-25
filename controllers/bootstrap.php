<?php

	namespace Controllers;

	use Tilwa\Route\RouteRegister;

	use Tilwa\Controllers\Bootstrap as InitApp;

	use Dotenv\Dotenv;

	use Doctrine\ORM\Tools\Setup;

	use Doctrine\ORM\EntityManager;
	
	
	class Bootstrap extends InitApp {

		protected function setConnection () {

			$dotenv = Dotenv::createImmutable( $this->container['rootPath'] );

			$dotenv->load();

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

				'siteName' => @$_SERVER['SERVER_NAME'], // is empty when running from cli
				'thisYear' => date('Y')

			] + compact('rootPath', 'slash');
		}

		protected function getInterfaceRepresentatives ():array {
			
			return [

				'Symfony\Bundle\MakerBundle\MakerInterface' => 'Symfony\Bundle\MakerBundle\Maker\MakeEntity'
			];
		}

		protected function foundUser (array $session, $apiToken = null) {

			$repo = new BaseEntityRepository(User::class, ['id' => $session['tilwa_id']]);
		}
	}

?>