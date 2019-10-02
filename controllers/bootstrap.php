<?php

	namespace Controllers;
	
	use Dotenv\Dotenv;

	use Nmeri\Tilwa\Route\RouteRegister;
	
	class Bootstrap {

		private $container;

		function __construct ( string $path) {

			$this->setStaticVars( compact('path') );
			
			$this->setConnection();

			$this->loadRoutes();
		}

		private function setConnection ( ) {

			$dotenv = Dotenv::create( $this->container['rootPath'] );

			$dotenv->load();

			try {

				$conn = new PDO("mysql:host=localhost;dbname=". getenv('DBNAME') . ";charset=utf8", getenv('DBUSER'), getenv('DBPASS'), array(PDO::ATTR_PERSISTENT => true));

				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				$conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false); // to retain int data type

				$this->container['connection'] = $conn; 
			}
			catch (PDOException $e) {

				var_dump("unable to connect to mysql server", $e->getMessage());
			}
		}

		private function setStaticVars ( $vars ) {

			$this->container = [

				'rootPath' => dirname(__DIR__, 1) . DIRECTORY_SEPARATOR, // up one folder

				'router' => new RouteRegister, 'classes' => [],

				'routesDirectory' => 'routes',

				'middlewareDirectory' => 'Middleware',

				'requestName' => $vars['path'],
			];
		}

		private function user () {

			if (!$this->container['user']) {

				session_start();

				$sess = $_SESSION;

				if (empty($sess)) $user = null;

				else {

					$ctrl = $this->getClass(GetController::class);

					$uColumn = $ctrl->getContentOptions()['primaryColumns']['user'];

					$user = $ctrl->getContents($sess[$uColumn], 'user');
				}

				$this->container['user'] = $user;
			}
		}

		private function loadRoutes ( ) {

			$registrar = $this->router;

			$groups = array_filter(
				scandir( $this->rootPath . $this->routesDirectory ), 

				function ($name) { return !in_array($name, ['.', '..']);}
			);

			// scan dir for all route files and pass them the registrar
			foreach ($groups as $file) require_once $file;
		}

		public function __get ($key) {

			if (array_key_exists($key, $this->container)) return $this->container[$key];

			if method_exists($this, $key) {

				$this->$key(); return $this->container[$key];
			}
		}

		public function getClass ( $fullName) {

			// search in 
			if (array_key_exists($fullName, $this->container['classes'])) return $this->container['classes'][$fullName];

			// if not there, grab class and load their params recursively
			$params = []; $constr =  new ReflectionMethod($fullName, '__construct'); $init = '';

			foreach ($constr->getParameters() as $param) {
				
				if ($param->allowsNull() || $param->isOptional() || $param->isDefaultValueAvailable()) $params[] = null;

				if ($param->hasType()) {

					$type = $param->getType();

					if ( $type->getName() == __CLASS__) $params[] = $this;

					elseif ( $type->isBuiltin()) {

						settype($init, $type); $params[] = $init;
					}

					else $params[] = $this->getClass($type);
				}
			}

			// params ready. instantiate and include in app container
			$this->container['classes'][$fullName] = call_user_func_array([$fullName, '__construct'], $params);

			return $this->container['classes'][$fullName];
		}
	}

?>