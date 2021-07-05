<?php

	namespace Tilwa\App;

	use ReflectionMethod, ReflectionClass, ReflectionFunction, ReflectionType, Exception;
	
	use Tilwa\App\Structures\{ProvisionUnit, NamespaceUnit};
	
	use Tilwa\App\Templates\CircularBreaker;

	use Tilwa\Contracts\Config\{ConfigMarker, Laravel as LaravelConfig, Services as ServicesConfig};

	use Tilwa\Bridge\Laravel\LaravelProviderManager;

	class Container {

		private $provisionedClasses = [], // ProvisionUnit[]

		$provisionedNamespaces = [], // NamespaceUnit[]

		$dependencyChain = [],

		$libraryConfigs = [],

		$universalSelector = "*",

		$laravelConfig, $servicesConfig,

		$provisionContext, // the active Type before calling `needs`

		$provisionSpace,

		$recursingFor; // the active ProvisionUnit

		public function __construct () {

			$this->provisionedClasses[$this->universalSelector] = new ProvisionUnit;
		}

		public function setConfigs (array $configs):self {

			$this->libraryConfigs = $configs;

			return $this;
		}

		/**
		*	Looks for the given class in this order
			*	1) pre-provisioned caller list
			*	2) Provisions it afresh if an interface or recursively wires in its constructor dependencies
		*
		*	@param {includeSub} With `needs`, you can supply sub-types in place of their parents, but with this, non-provisioned sub-types can access provisions of their superiors
		* @return A class instance if found
		*/
		public function getClass (string $fullName, bool $includeSub = false) {
var_dump($fullName);
			$contextConcrete = $this->getClassForContext($fullName);

			if (!is_null($contextConcrete))

				return $contextConcrete;

			if ($includeSub && $parent = $this->hydrateChildsParent($fullName))

				return $parent;

			$servicesConfig = $this->getServicesConfig();

			if (!is_null($servicesConfig)) {

				var_dump($servicesConfig->usesLaravelPackages(), $this->laravelHas($fullName), $fullName);

				if ($servicesConfig->usesLaravelPackages() && $this->laravelHas($fullName))

					return $this->loadLaravelLibrary($fullName);
			}

			$reflectedClass = new ReflectionClass($fullName);

			if ($reflectedClass->isInterface())

				return $this->provideInterface($fullName);

			if ($reflectedClass->isInstantiable())

				return $this->instantiateConcrete($fullName);
		}

		public function getClassForContext (string $fullName) {

			$isOriginalCall = false;

			if (is_null($this->recursingFor)) {

				$isOriginalCall = true;

				$this->recursingFor = $this->lastCaller();
			}

			else $this->recursingFor = $fullName; // e.g if we are hydrating dependency B of class A, we want to get provisions for B, not A, the last called

			$context = $this->getRecursionContext();

			if ($context->hasConcrete($fullName)) {

				if ($isOriginalCall) $this->resetAsker();

				return $context->getConcrete($fullName);
			}
		}

		private function loadLaravelLibrary(string $fullName ):object {

			$providers = $this->getLaravelConfig()->getProviders();

			$laravelApp = $this->getClass(LaravelApp::class);

			$provider = new $providers[$fullName]($laravelApp);

			$instance = (new LaravelProviderManager($provider, $laravelApp, $this))

			->prepare()->getConcrete();

			if ($fullName == get_class($instance)) {

				$this->storeConcrete( $fullName, $instance);

				return $instance;
			}
		}

		private function hydrateChildsParent(string $fullName):object {

			$providedParent = $this->getProvidedParent($fullName);

			if (!is_null($providedParent))

				return $this->getClass($providedParent);
		}

		private function instantiateConcrete (string $fullName):object { // casting to [object] may be problematic to the caller

			$this->dependencyChain[] = $fullName;
			
			$dependencies = $this->getMethodParameters("__construct", $fullName);

			$concrete = new $fullName (...$dependencies);

			$this->unchainDependency($fullName);

			$this->storeConcrete($fullName, $concrete);

			return $concrete;
		}

		private function storeConcrete (string $fullName, object $concrete):void {

			$this->provisionedClasses[$this->recursingFor]

			->addConcrete($fullName, $concrete);
		}

		private function lastCaller ():string {

			$stack = debug_backtrace (2 ); // 2=> ignore concrete objects and their args

			$caller = "class";

			foreach ($stack as $execution)

				if (array_key_exists($caller, $execution) && $execution[$caller] != get_class()) {

					return $execution[$caller];
				}
		}

		/**
		* Switches unit being provided to universal if it doesn't exist
		* @return currently available provision unit
		*/
		private function getRecursionContext():ProvisionUnit {

			if (!array_key_exists($this->recursingFor, $this->provisionedClasses))

				$this->recursingFor = $this->universalSelector;

			return $this->provisionedClasses[$this->recursingFor];
		}

		private function provideInterface(string $service):object {

			if ($this->isRenamedSpace()) {

				$newIdentity = $this->relocateSpace($service);

				return $this->instantiateConcrete($newIdentity);
			}

			if ($this->isConfig($service))

				return $this->hydrateConfig($service);

			return $this->getClassFromProvider($service);
		}

		private function getClassFromProvider (string $service) {

			$config = $this->getServicesConfig();

			if (is_null($config)) return;

			$providers = $config->getProviders();

			if (!array_key_exists($service, $providers)) return;

			$providerClass = $providers[$service];

			$provider = $this->instantiateConcrete($providerClass);

			$providerParameters = $provider->bindArguments();

			$concrete = $provider->concrete();

			$this->whenType($concrete) // merge any custom args with defaults

			->needsArguments($providerParameters);
				
			$reflectedClass = $this->getClass($concrete);

			$provider->afterBind($reflectedClass);

			$this->storeConcrete ($service, $reflectedClass);

			if ($reflectedClass instanceof $service)

				return $reflectedClass;
		}

		public function whenType (string $toProvision):self {

			if (!array_key_exists($toProvision, $this->provisionedClasses))

				$this->provisionedClasses[$toProvision] = new ProvisionUnit;

			$this->provisionContext = $toProvision;

			return $this;
		}

		public function whenTypeAny ():self {

			return $this->whenType($this->universalSelector);
		}

		public function needs (array $dependencyList):self {

			if (is_null ($this->provisionContext))

				throw new Exception("Undefined provisionContext");

			$this->provisionedClasses[$this->provisionContext]->updateConcretes($dependencyList);
			
			return $this;
		}

		public function needsAny (array $dependencyList):self {

			$this->needs($dependencyList)

			->needsArguments($dependencyList);

			$this->provisionContext = null;

			return $this;
		}

		public function needsArguments (array $argumentList):self {

			if (is_null ($this->provisionContext))

				throw new Exception("Undefined provisionContext");

			$this->provisionedClasses[$this->provisionContext]->updateArguments($argumentList);
			
			return $this;
		}

		/**
		*	Fetch appropriate dependencies for a callable's arguments
		* @param {callable}:string|Closure
		* @param {anchorClass} the class the given method belongs to
		* @return {Array} associative. Contains hydrated parameters to invoke given callable with
		*/ 
		public function getMethodParameters ( $callable, string $anchorClass):array {

			$context;

			$dependencies = [];

			$externalCall = false;

			if (isset($anchorClass)) {

				$reflectedCallable = new ReflectionMethod($anchorClass, $callable);

				if (is_null($this->recursingFor)) {

					$externalCall = true;

					$this->recursingFor = $anchorClass; // Assume class A wanna get parameters for class B->foo. When set to `lastCaller`, we'll be looking through class A's provisions instead of class B
				}

				$context = $this->getRecursionContext();
			}
			else $reflectedCallable = new ReflectionFunction($callable);

			foreach ($reflectedCallable->getParameters() as $parameter) {

				$parameterName = $parameter->getName();

				$parameterType = $parameter->getType();

				$matchesArgument = $context->hasArgument($parameterName) || $context->hasArgument($parameterType);

				if ($context && $matchesArgument)

					$dependencies[$parameterName] = $context->getArgument($parameterName);

				elseif ($parameterType)

					$dependencies[$parameterName] = $this->getParameterValue($parameterType);
				
				elseif ($parameter->isOptional() )

					$dependencies[$parameterName] = $parameter->getDefaultValue();

				else $dependencies[$parameterName] = null;
			}

			if ($externalCall) $this->resetAsker();

			return $dependencies;
		}

		private function getParameterValue(ReflectionType $parameterType) {

			$typeName = $parameterType->getName();

			if ( !$parameterType->isBuiltin()) {

				if (!in_array($typeName, $this->dependencyChain))

					return $this->getClass($typeName);

				return $this->genericFactory(
					CircularBreaker::class, 

					["target" => $typeName ],

					function ($types) {

				    	return new CircularBreaker($types["target"], $this);
					}
				); // A requests B and vice versa. If A makes the first call, we're returning a proxied/fake A to the B instance we pass to the real A
			}

			$defaultValue = null;

			settype($defaultValue, $typeName);

			return $defaultValue;
		}

		// @return the first provided parent of the given class
		private function getProvidedParent(string $class):string {

			$allSuperiors = array_keys($this->provisionedClasses);

			$classSuperiors = class_parents($class, true) +class_implements($class, true);

			$providedParents = array_intersect($classSuperiors, $allSuperiors);

			if (!empty($providedParents))

				return current($providedParents);
		}

		public function whenSpace(string $callerNamespace):self {

			if (!array_key_exists($callerNamespace, $this->provisionedNamespaces))

				$this->provisionedNamespaces[$callerNamespace] = [];

			$this->provisionSpace = $callerNamespace;

			return $this;
		}

		private function renameServiceSpace(NamespaceUnit $unit):self {
			
			$this->provisionedNamespaces[$this->provisionSpace][] = $unit;

			return $this;
		}

		private function isRenamedSpace():bool {

			$namespace = $this->localizeNamespace($this->recursingFor, 1);
			
			return array_key_exists($namespace, $this->provisionedNamespaces);
		}

		private function relocateSpace(string $entityFormerName):string {

			$locality = $this->localizeNamespace($this->recursingFor, 1);
			
			$context = $this->provisionedNamespaces[$locality];

			$entityParent = $this->localizeNamespace($entityFormerName, 1);

			foreach ($context as $spaceUnit) {
				
				if ($spaceUnit->getSource() == $entityParent) {

					$requestedEntity = $this->getRequestedEntity();

					return $spaceUnit->getLocation() . "\\". $spaceUnit->getNewName($requestedEntity);
				}
			}
		}

		/**
		*	@param {backStep} Given a [fullName] Space1\Space2\TargetNamespace\Target, we want the anchor "Space1\Space2" when this is 2
		*/
		private function localizeNamespace(string $fullName, int $backStep):string {
			
			return implode("\\", (explode("\\", $fullName, -$backStep)));
		}

		private function getRequestedEntity(string $fullName):string {
			
			return end(explode("\\", $fullName));
		}

		/**
		*	@return Result of evaluating {constructor}
		*/
		public function genericFactory (string $generic, array $types, callable $constructor):object {

			$reflectedGeneric = new ReflectionClass($generic);

			$genericContents = file_get_contents($reflectedGeneric->getFileName());

		    foreach ($types as $placeholder => $type)

		        $genericContents = str_replace("<$placeholder>", $type, $genericContents);

		    eval($genericContents);

		    return $constructor($types);
		}

		private function unchainDependency(string $fullName):void {
			
			$this->dependencyChain = array_filter($this->dependencyChain, function ($dependency) use ($fullName) {

				return $dependency != $fullName;
			});
		}

		private function isConfig(string $service):bool {
			
			return in_array(ConfigMarker::class, class_implements($service));
		}

		// this just seems to be a shortcut to the [needs] group of methods, but doesn't want to muddle config provisioning with every other class type
		private function hydrateConfig(string $fullName) {

			$configs = $this->libraryConfigs;
var_dump($configs, $fullName);
			if (!array_key_exists($fullName, $configs)) return;
				
			return $this->instantiateConcrete($configs[$fullName]); // classes can't have custom config
		}

		// using this to subvert the arduous process of class hydration
		private function getLaravelConfig():LaravelConfig {

			if (is_null($this->laravelConfig))

				$this->laravelConfig = $this->hydrateConfig(LaravelConfig::class);

			return $this->laravelConfig;
		}

		private function getServicesConfig():?ServicesConfig {

			if (is_null($this->servicesConfig))

				$this->servicesConfig = $this->hydrateConfig(ServicesConfig::class);

			return $this->servicesConfig;
		}

		private function resetAsker ():void {

			$this->recursingFor = null; // ahead of future invocations by other callers to modular Container
		}

		private function laravelHas (string $fullName):bool {

			return array_key_exists(
				$fullName,

				$this->getLaravelConfig()->getProviders()
			);
		}
	}
?>