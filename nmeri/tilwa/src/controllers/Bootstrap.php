<?php

	namespace Tilwa\Controllers;
	
	use Dotenv\Dotenv;

	use PDO;

	use ReflectionMethod;

	use ReflectionClass;

	
	class Bootstrap {

		/* @var array */
		protected $container;

		/* @var bool */
		private $refresh;

		function __construct ( string $path) {

			$this->setStaticVars( compact('path') );
			
			$this->setConnection();

			$this->loadRoutes();
		}

		protected function setConnection ( ) {

			$this->container['connection'] = null;
		}

		protected function setStaticVars ( $vars ) {

			return $vars;
		}

		private function user () {

			if (!isset($this->user)) {

				session_start(); // THIS SHOULD ONLY RUN IN THE ABSENCE OF A BEARER TOKEN
				$sess = $_SESSION;

				if (empty($sess)) $user = null;

				else $user = $this->foundUser($sess, @getallheaders()['Authorization']);

				$this->container['user'] = $user;
			}
		}

		private function loadRoutes ( ) {

			$registrar = $this->router;

			$pathName = $this->rootPath . $this->routesDirectory;

			$groups = array_filter( scandir($pathName), function ($name) {

				return !in_array($name, ['.', '..']);
			});

			// scan dir for all route files and pass them the registrar
			foreach ($groups as $file) require_once $pathName . $this->container['slash'] . $file;
		}

		public function __get ($key) {

			if (array_key_exists($key, $this->container) && $this->refresh !== true) return $this->container[$key];

			if (method_exists($this, $key)) {

				$this->$key(); return $this->container[$key];
			}

			// lastly assume user trying to get class
			if ($isClass = $this->getClass($key)) return $isClass;

			return null;
		}

		/**
		* @description Will load the instance in the app classes cache
		*
		*@return A class instance if found
		*/
		public function getClass (string $fullName) {

			if (array_key_exists($fullName, $this->container['classes']))

				return $this->container['classes'][$fullName];

			// if not there, grab class and load their constructorParams recursively
			$constructorParams = [];

			$init = '';

			$refleClass = new ReflectionClass($fullName);

			if ($refleClass->isInterface()) { // switch to an implementation

				$fullName = $this->getInterfaceRepresentatives()[$fullName];
				
				$refleClass = new ReflectionClass($fullName);
			}

			if ($refleClass->isInstantiable())

				$constr = $refleClass->getConstructor();

			else $constr = null; // we'll assume this is an abstract class

			if (!is_null($constr)) foreach ($constr->getParameters() as $param) {
				
				if ($param->allowsNull() )

					$constructorParams[] = null;

				elseif ($param->isOptional() ) {

					if (!$param->isDefaultValueAvailable()) // is it possible for this to be false? may return true if default is null

						$constructorParams[] = null;

					else $constructorParams[] = $param->getDefaultValue();
				}

				elseif ($param->hasType()) {

					$type = $param->getType();

					$typeName = $type->getName();

					if ( $typeName == __CLASS__) $constructorParams[] = $this;

					elseif ( $type->isBuiltin()) {

						settype($init, $type); $constructorParams[] = $init;
					}

					elseif (($type === []) || $type->getName() == 'array' ) $constructorParams[] = [];

					else {
						
						$res = $this->getClass($typeName);
						
						$constructorParams[] = $res;
					}
				}
			}

			// constructorParams ready. instantiate and include in app container
			$classInst = new $fullName ( ...$constructorParams);
			
			return $this->container['classes'][$fullName] = $classInst;
		}

		public function fresh ($prop) {

			$this->refresh = true;

			$val = $this->$prop;

			$this->refresh = false;

			return $val;
		}

		/**
		* @description defines the process of obtaining user
		* @return a user model/entity streamline to your orm
		*/
		protected function foundUser (array $session, $apiToken = null) {}

		/**
		* @return an array containing what implementation to serve to the container when presented with multiple implementations of an interface*/
		protected function getInterfaceRepresentatives ():array {

			//
		}
	}

?>