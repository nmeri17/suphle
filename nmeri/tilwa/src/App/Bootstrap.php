<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	use PDO;

	use ReflectionMethod;

	use ReflectionClass;

	use Tilwa\Routing\Route;

	abstract class Bootstrap {

		/**
		* @property array */
		protected $container;

		/* @property bool */
		private $refresh;

		public $routeCatalog;

		public $router;

		private $classes = [];

		private $databaseAdapter;

		function __construct () {

			$this->setFileSystemPaths()->loadEnv()

			->setConnection()

			// ->configMail() // we only wanna run this if it's not set already and if dev wanna send mails. so, a mail adapter?

			->getAppMainRoutes()->initSession();

			$this->routeCatalog = new RouteRegister;

			$this->router = new RouteManager($this);
		}

		protected function setConnection () {

			return $this;
		}

		abstract function setFileSystemPaths ():self;

		abstract function getAppMainRoutes():string;

		/**
		* @return an array containing what implementation to serve to the container when presented with multiple implementations of an interface
		*/
		abstract protected function boundServices ():array;

		private function user () {

			if (!isset($this->user)) {

				$headers = getallheaders();

				$headerKey = "Authorization";

				$identifier = null;

				if (array_key_exists($headerKey, $headers))

					$identifier = $headers[$headerKey]; // this should be deserialized before assignment
				else $identifier = $_SESSION['tilwa_user_id'];

				if (!$identifier) $user = null;

				else $user = $this->getClass(Orm::class)->getUser(); // remember to set identifier on this guy

				$this->container['user'] = $user;
			}
		}

		public function __get ($key) {

			if (
				array_key_exists($key, $this->container) &&

				$this->refresh !== true
			)
				return $this->container[$key];

			if (method_exists($this, $key)) {

				$this->refresh = false; // so other dependents don't try getting fresh copies too

				$this->$key();

				return $this->container[$key];
			}

			// lastly assume dev trying to get class
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

			return $val;
		}

		protected function loadEnv () {		

			$dotenv = Dotenv::createImmutable( $this->container['rootPath'] );

			$dotenv->load();

			return $this;
		}

		protected function configMail () {

			ini_set("SMTP", getenv('MAIL_SMTP'));

			ini_set("smtp_port", getenv('MAIL_PORT'));

			ini_set('sendmail_from', getenv('MAIL_SENDER'));

			return $this;
		}

		private function initSession () {

			if (session_status() == PHP_SESSION_NONE /*&& !headers_sent()*/)

				session_start(); //session_destroy(); $_SESSION = [];

			return $this;
		}

		public function whenType (string $type) {

			// we're working with debug_backtrace()

			// and $this->container['classes']
		}

		public function needsArgument (string $type) {

			//
		}

		public function giveArguments (array $arguments) {

			//
		}

		public function needs (string $type) {

			// ensure the given type is an instance of current/active whenType
		}

		public function give ( $valueObject) {

			// should throw an error if no active needs[Arg]
			// work with `this.getInterfaceRepresentatives`
		}

		/**
		* @ description: fetch appropriate classes for a method's arguments
		* @param {method}:string|Closure
		* @return {Array} of hydrated parameters to call given method with
		*/ 
		public function getMethodParameters ( $method, string $class) {

			// class is disregarded when method= closure

			// still works with `this.getClass` (or, at least, borrows same mechanism) but that guy works with the constructor directly, so you can pass in a method name from here (or default to constructor). @see line 130
		}
	}

?>