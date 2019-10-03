<?php

	namespace Controllers;
	
	use Dotenv\Dotenv;

	
	class Bootstrap {

		/* @param array */
		private $container;

		/* @param bool */
		private $refresh;

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

		protected function setStaticVars ( $vars ) {

			return $vars;
		}

		private function user () {

			if (!$this->container['user']) {

				session_start();

				$sess = $_SESSION;

				if (empty($sess)) $user = null;

				else {

					$ctrl = $this->getClass( GetController::class);

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

			if (array_key_exists($key, $this->container) && $this->refresh !== true) return $this->container[$key];

			if method_exists($this, $key) {

				$this->$key(); return $this->container[$key];
			}

			// lastly assume user trying to get class
			if ($isClass = $this->getClass($key)) return $isClass;

			return null;
		}

		public function getClass ( $fullName) {

			// search in 
			if (array_key_exists($fullName, $this->container['classCache'])) return $this->container['classCache'][$fullName];

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

					elseif (($type === []) || $type->getName() == 'array' ) $params[] = [];

					else $params[] = $this->getClass($type);
				}
			}

			// params ready. instantiate and include in app container
			$this->container['classCache'][$fullName] = new $fullName ( ...$params);

			return $this->container['classCache'][$fullName];
		}

		public function fresh ($prop) {

			$this->refresh = true;

			$val = $this->$prop;

			$this->refresh = false;

			return $val;
		}
	}

?>