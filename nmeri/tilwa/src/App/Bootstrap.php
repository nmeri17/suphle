<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	use PDO;

	use ReflectionMethod;

	use ReflectionClass;

	use Models\User;

	use Tilwa\Contracts\{Orm, HtmlParser, Authenticator};

	use Tilwa\ServiceProviders\{OrmProvider, AuthenticatorProvider, HtmlTemplateProvider};

	use Tilwa\Http\Request\RouteGuards;

	abstract class Bootstrap {

		/* @property bool */
		private $refresh;

		public $router;

		private $classes = [];

		function __construct () {

			$this->loadEnv()->initSession()->provideSelf();

			// ->configMail() // we only wanna run this if it's not set already and if dev wanna send mails. so, a mail adapter?
		}

		// should be listed in descending order of the versions
		public function apiStack ():array;

		public function browserEntryRoute ():string;

		public function getRootPath ():string;

		protected function getServiceProviders ():array {

			return [
				Orm::class => OrmProvider::class,

				HtmlParser::class => HtmlTemplateProvider::class,

				Authenticator::class => AuthenticatorProvider::class,

				RequestValidator::class => RequestValidatorProvider::class
			];
		}

		// will supply the active module to every client requesting this base type
		public function provideSelf ():self;

		/**
		* @description Will load the instance in the app classes cache
		*
		*@return A class instance if found
		*/
		public function getClass (string $fullName) {

			if (array_key_exists($fullName, $this->classes))

				return $this->classes[$fullName];

			// if not there, grab class and load their constructorParams recursively
			$constructorParams = [];

			$init = '';

			$refleClass = new ReflectionClass($fullName);

			if ($refleClass->isInterface()) { // switch to an implementation

				$fullName = $this->providers()[$fullName]; // this was refactored, so review this block
				
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

			// constructorParams ready. instantiate and include in app classes
			$classInst = new $fullName ( ...$constructorParams);
			
			return $this->classes[$fullName] = $classInst;
		}

		public function fresh ($prop) {

			$this->refresh = true;

			$val = $this->$prop;

			return $val;
		}

		protected function loadEnv () {		

			$dotenv = Dotenv::createImmutable( $this->getRootPath() );

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

			// after pairing this, if we're getting a class manually instead of injecting it as method argument, `getClass` will have to check debug_backtrace() for whether caller matches what we passed here
		}

		public function whenTypeAny () {

			return $this->whenType("*");
		}

		public function needsArguments (string $type) {

			//
		}

		public function giveArguments (array $arguments) {

			//
		}

		public function needs (string $type) {

			// ensure the given type is an instance of current/active whenType
		}

		// @param {$valueObject} instance of singleton
		public function give ( $valueObject) {

			// should throw an error if no active needs[Arg]
			// work with `this->getServiceProviders()`
		}

		public function needsArgumentsType (string $type) {

			//
		}

		public function needsAny (string $type) {

			// activates both arguments and normal needs ahead of the give call
		}

		/**
		* @ description: fetch appropriate classes for a method's arguments
		* @param {method}:string|Closure
		* @return {Array} of hydrated parameters to call given method with
		*/ 
		public function getMethodParameters ( $method, string $class):array {

			// class is disregarded when method= closure

			// still works with `this->getClass` (or, at least, borrows same mechanism) but that guy works with the constructor directly, so you can pass in a method name from here (or default to constructor). @see line 130

			// if you don't liaise with `this->getClass` first check if class has been previously loaded. if it's not there, plug it into our `classes` array

			// try and key the final array by the parameter name instead of a numeric array of values
		}

		public function setDependsOn(array $bindings):self {
			
			# check if key interface matches the `exports` of incoming type before pairing
		}

		// @return interfaces[] from `Interactions` namespace
		public function getDependsOn():array {

			return [];
		}

		public function exports():string {

			return; // an interface from Interactions namespace for `setDependsOn` on sister modules to consume
		}

		public function getUserModel():string {

			return User::class;
		}

		public function apiPrefix():string {

			return "api";
		}

		public function getViewPath ():string {

			return $this->getRootPath() . 'views'. DIRECTORY_SEPARATOR;
		}

		# class containing route guard rules
		public function routePermissions():object {
			
			return $this->getClass(RouteGuards::class);
		}
	}

?>