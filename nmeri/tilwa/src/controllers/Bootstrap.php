<?php

	namespace Tilwa\Controllers;
	
	use Dotenv\Dotenv;

	use PDO;

	use ReflectionMethod;

	use ReflectionClass;

	use Tilwa\Route\Route;

	
	class Bootstrap {

		/**
		* @property array */
		protected $container;

		/* @property bool */
		private $refresh;

		function __construct ( array $config = []) {

			$this->setStaticVars( $config )->loadEnv()

			->setConnection()->configMail()

			->loadRoutes()->initSession();
		}

		protected function setConnection ( ) {

			$this->container['connection'] = null;

			return $this;
		}

		protected function setStaticVars ( $vars ) {

			return $this;
		}

		private function user () {

			if (!isset($this->user)) {

				$bearer = @getallheaders()['Authorization'];

				if (!$bearer) {

					$sess = $_SESSION;
				}

				if (empty($sess) && !$bearer) $user = null;

				else $user = $this->foundUser($sess, $bearer);

				$this->container['user'] = $user;
			}
		}

		private function loadRoutes () {

			$registrar = $this->routeCatalog;

			$pathName = $this->rootPath . $this->routesDirectory;

			$groups = array_filter( scandir($pathName), function ($name) {

				return !in_array($name, ['.', '..']);
			});

			// scan dir for all route files and pass them the registrar
			foreach ($groups as $file)

				require_once $pathName . $this->container['slash'] . $file;

			return $this;
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

					elseif (($type === []) || $type->getName() == 'array' ) {$constructorParams[] = [];var_dump($fullName); die();} // wonder if we ever get here

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
		* @return a user model/entity streamlined to your orm
		*/
		protected function foundUser ($session, string $apiToken = null) {
			// non-browser devices will be unable to retain session, so we expect to use a token to maintain user state
		}

		/**
		* @return an array containing what implementation to serve to the container when presented with multiple implementations of an interface*/
		protected function getInterfaceRepresentatives ():array {

			return [];
		}

		public function setSingleton (string $typeName, $default) {

			$this->container['classes'][$typeName] = $default;

			return $this;
		}

		protected function loadEnv () {		

			$dotenv = Dotenv::createImmutable( $this->container['rootPath'] );

			$dotenv->load();

			return $this;
		}

		protected function configMail () {

			ini_set("SMTP", getenv('MAILSMTP'));

			ini_set("smtp_port", getenv('MAILPORT'));

			ini_set('sendmail_from', getenv('MAILSENDER'));

			return $this;
		}

		private function initSession () {

			if (session_status() == PHP_SESSION_NONE /*&& !headers_sent()*/)

				session_start(); //session_destroy(); $_SESSION = [];

			return $this;
		}
	}

?>