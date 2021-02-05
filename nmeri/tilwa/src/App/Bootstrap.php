<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	use {PDO, ReflectionMethod, ReflectionClass, ReflectionFunction, ReflectionType};

	use Models\User;

	use Tilwa\Contracts\{Orm, HtmlParser, Authenticator, RequestValidator};

	use Tilwa\ServiceProviders\{OrmProvider, AuthenticatorProvider, HtmlTemplateProvider, RequestValidatorProvider};

	use Tilwa\Http\Request\RouteGuards;

	abstract class Bootstrap {

		private $provisionedClasses; // list of `providerTemplate`s

		private $provisionContext; // the active Type before calling `needs`

		private $recursingFor; // the active `providerTemplate`

		function __construct () {

			$this->loadEnv()->initSession()->provideSelf();

			// ->configMail() // we only wanna run this if it's not set already and if dev wanna send mails. so, a mail adapter?

			$this->provisionedClasses = [];
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
		* @description: Looks for the given class in this order
			1) pre-provisioned caller list
			2) Provisions it afresh if an interface or recursively wires in its constructor dependencies
		*
		* @return A class instance if found
		*/
		public function getClass (string $fullName) {

			if (is_null($this->recursingFor))

				$this->recursingFor = $this->lastCaller();

			else $this->recursingFor = $fullName;

			$context = $this->getRecursionContext();

			if (array_key_exists($fullName, $context["concretes"]))

				return $context["concretes"][$fullName];

			$reflectedClass = new ReflectionClass($fullName);

			if ($reflectedClass->isInterface())

				return $this->provideInterface($fullName);

			if ($reflectedClass->isInstantiable()) {
				
				$dependencies = $this->getMethodParameters($reflectedClass->getConstructor(), $fullName);

				return $this->provisionedClasses[$this->recursingFor]["concretes"][$fullName] = new $fullName (...$dependencies);
			}
		}

		private function lastCaller ():string {

			$stack = debug_backtrace ( DEBUG_BACKTRACE_IGNORE_ARGS, 3 ); // [lastCaller,getClass,ourGuy]. Another extensible but memory intensive way to go about this is to group the calls by class and pick the immediate last one that doesn't correspond to `self::class`

			return end($stack)["class"];
		}

		/**
		* @description switches template being provided to universal if it doesn't exist
		* @return currently available provision template
		*/
		private function getRecursionContext():array {

			if (!array_key_exists($this->recursingFor, $this->provisionedClasses))

				$this->recursingFor = "*";

			return $this->provisionedClasses[$this->recursingFor];
		}

		private function provideInterface(string $service):object {

			$providerClass = $this->getServiceProviders()[$service];

			$provider = new $providerClass();

			$providerArguments = $this->getMethodParameters("bindArguments", $providerClass);

			$providerParameters = call_user_func_array([$provider, "bindArguments"], $providerArguments);

			$concrete = $provider->concrete();

			$this->whenType($concrete) // for easy instantiation by getClass

			->needsArguments($this->wrapInCallable( $providerParameters));
				
			$reflectedClass = $this->getClass($concrete);

			$provider->afterBind($reflectedClass);

			$this->whenTypeAny()

			->needsAny([$service => $reflectedClass]);

			return $reflectedClass;
		}

		private function wrapInCallable(array $values):array {
			
			return array_map(function ($value) {
				return function () use ($value) {

					return $value;
				};
			}, $values);
		}

		protected function loadEnv () {		

			$dotenv = Dotenv::createImmutable( $this->getRootPath() );

			$dotenv->load();

			return $this;
		}

		protected function configMail ():self {

			ini_set("SMTP", getenv('MAIL_SMTP'));

			ini_set("smtp_port", getenv('MAIL_PORT'));

			ini_set('sendmail_from', getenv('MAIL_SENDER'));

			return $this;
		}

		private function initSession ():self {

			if (session_status() == PHP_SESSION_NONE /*&& !headers_sent()*/)

				session_start(); //session_destroy(); $_SESSION = [];

			return $this;
		}

		public function whenType (string $toProvision):self {

			if (!array_key_exists($toProvision, $this->provisionedClasses))

				$this->provisionedClasses[$toProvision] = $this->providerTemplate($overwritable);

			$this->provisionContext = $toProvision;

			return $this;
		}

		public function whenTypeAny ():self {

			return $this->whenType("*");
		}

		public function needs (array $dependencyList):self {

			return $this->populateProvisioner($dependencyList, "concretes" );
		}

		public function needsAny (array $dependencyList, bool $overwritable):self {

			$this->populateProvisioner($dependencyList, "concretes");

			return $this->populateProvisioner($this->wrapInCallable( $dependencyList), "arguments", $overwritable);
		}

		public function needsArguments (array $argumentList, bool $overwritable):self {

			return $this->populateProvisioner($argumentList, "arguments", $overwritable);
		}

		/**
		* @param {overwritable} when false, preserves any previous concrete given for these arguments
		*/
		private function populateProvisioner (array $parameters, string $mode, bool $overwritable=true) {

			$context = $this->provisionedClasses[$this->provisionContext];

			$modeArray = $context[$mode];

			foreach ($parameters as $name => $provide) {

				if (!array_key_exists($name, $modeArray) || !$context["overwritable"])

					if ($mode == "arguments")

						$modeArray[$name] = $provide($this); // arguments are defined as callbacks since they have the preservation option

					else $modeArray[$name] = $provide;
			}
			$context[$mode] = $modeArray;

			$this->provisionedClasses[$this->provisionContext] = $context;
		}

		// blueprint for each provided entity
		private function providerTemplate(bool $overwritable):array {
			
			return [
				"concretes" => [], // populated by `needs`
				"arguments" => [] // sent in as an associative array of closures invoked during an override
			] + compact("overwritable");
		}

		/**
		* @ description: fetch appropriate dependencies for a callable's arguments
		* @param {callable}:string|Closure
		* @param {anchorClass} the class the given method belongs to
		* @return {Array} associative. Contains hydrated parameters to invoke given callable with
		*/ 
		public function getMethodParameters ( $callable, string $anchorClass):array {

			$predefinedArguments = $dependencies = [];

			if (isset($anchorClass)) {

				$reflectedCallable = new ReflectionMethod($anchorClass, $callable);

				if (is_null($this->recursingFor))

					$this->recursingFor = $anchorClass; // Assume class A wanna get parameters for class B->foo. When set to `lastCaller`, we'll be looking through class A's provisions instead of class B

				$predefinedArguments = $this->getRecursionContext()["arguments"];
			}
			else $reflectedCallable = new ReflectionFunction($callable);

			foreach ($reflectedCallable->getParameters() as $parameter) {

				$parameterName = $parameter->getName();

				if (array_key_exists($parameterName, $predefinedArguments))

					$dependencies[$parameterName] = $predefinedArguments[$parameterName];
				
				if ($parameter->isOptional() )

					$dependencies[$parameterName] = $parameter->getDefaultValue();
				elseif ($parameter->hasType())

					$dependencies[$parameterName] = $this->getParameterValue($parameter->getType());
				else $dependencies[$parameterName] = null;
			}
			return $dependencies;
		}

		private function getParameterValue(ReflectionType $parameterType) {

			$typeName = $parameterType->getName();

			if ( !$parameterType->isBuiltin())

				return $this->getClass($typeName);

			$defaultValue = null;

			settype($defaultValue, $typeName);

			return $defaultValue;
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
		public function routePermissions():string {
			
			return RouteGuards::class;
		}
	}

?>