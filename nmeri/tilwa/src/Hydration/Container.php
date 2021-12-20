<?php
	namespace Tilwa\Hydration;

	use ReflectionMethod, ReflectionClass, ReflectionFunction, ReflectionType, ReflectionFunctionAbstract, Exception;
	
	use Tilwa\Hydration\Structures\{ProvisionUnit, NamespaceUnit};
	
	use Tilwa\Hydration\Templates\CircularBreaker;

	use Tilwa\Bridge\Laravel\LaravelProviderManager;

	use Tilwa\Errors\InvalidImplementor;

	class Container {

		private $provisionedClasses = [], // ProvisionUnit[]

		$provisionedNamespaces = [], // NamespaceUnit[]

		$dependencyChain = [],

		$universalSelector = "*",

		$laravelHydrator, $interfaceHydrator,

		$provisionContext, // the active Type before calling `needs`

		$provisionSpace, // same as above, but for namespaces

		$recursingFor; // string of the active ProvisionUnit

		public function __construct () {

			$this->provisionedClasses[$this->universalSelector] = new ProvisionUnit;
		}

		public function setInterfaceHydrator (string $collection):void {

			$concrete = $this->instantiateConcrete($collection);

			$this->interfaceHydrator = new InterfaceHydrator($concrete, $this);
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

			$contextConcrete = $this->getClassForContext($fullName);

			if (!is_null($contextConcrete)) return $contextConcrete;

			if ($includeSub && $parent = $this->hydrateChildsParent($fullName))

				return $parent;

			$concrete;

			$outermost = $this->notRecursing();

			$this->setRecursingFor($fullName);

			$concrete = $this->loadLaravelLibrary($fullName);

			if (is_null($concrete)) {

				$reflectedClass = new ReflectionClass($fullName);

				if ($reflectedClass->isInterface())

					$concrete = $this->provideInterface($fullName);

				if ($reflectedClass->isInstantiable())

					$concrete = $this->instantiateConcrete($fullName);
			}

			if ($outermost) $this->unsetRecursingFor();

			return $concrete;
		}

		private function getClassForContext (string $fullName) {

			$outermost = $this->notRecursing();

			$this->setRecursingFor($fullName, $outermost);

			$context = $this->getRecursionContext();

			if ($outermost) $this->unsetRecursingFor();

			if ($context->hasConcrete($fullName))

				return $context->getConcrete($fullName);
		}

		private function setRecursingFor (string $fullName, bool $isOutermost = false):void {

			if ($isOutermost)

				$this->recursingFor = $this->lastCaller();

			else $this->recursingFor = $fullName; // e.g if we are hydrating dependency B of class A, we want to get provisions for B, not A, the last called
		}

		private function loadLaravelLibrary( string $fullName ) {

			$hydrator = $this->getLaravelHydrator();

			if (!$hydrator->canProvide($fullName))

				return null;

			$concrete = $hydrator->manageService($fullName);

			$this->saveWhenImplements($fullName, $concrete);

			return $concrete;
		}

		private function saveWhenImplements (string $interface, $concrete):void {

			if (!($concrete instanceof $interface))

				throw new InvalidImplementor($interface, get_class($concrete));

			$this->storeConcrete( $interface, $concrete);
		}

		private function hydrateChildsParent(string $fullName):?object {

			$providedParent = $this->getProvidedParent($fullName);

			if (is_null($providedParent)) return null;

			return $this->getClass($providedParent);
		}

		public function instantiateConcrete (string $fullName) {

			$constructor = "__construct";

			if (method_exists($fullName, $constructor)) {

				$this->dependencyChain[] = $fullName;
			
				$dependencies = array_values($this->getMethodParameters($constructor, $fullName));

				$concrete = new $fullName (...$dependencies);

				$this->unchainDependency($fullName);
			}

			else $concrete = new $fullName;

			$this->storeConcrete($fullName, $concrete);

			return $concrete;
		}

		private function storeConcrete (string $fullName, object $concrete):void {

			$this->getRecursionContext();

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

				$this->setRecursingFor($this->universalSelector);

			return $this->provisionedClasses[$this->recursingFor];
		}

		/**
		 * @return Concrete of the given [Interface] if it has been provided
		 * */
		private function provideInterface(string $interface) {

			if ($this->isRenamedSpace()) {

				$newIdentity = $this->relocateSpace($interface);

				return $this->instantiateConcrete($newIdentity);
			}

			$concrete = $this->interfaceHydrator->deriveConcrete($interface);

			if (is_null($concrete)) return;

			$this->saveWhenImplements($fullName, $concrete);

			return $concrete;
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

			$explicitCall = $this->notRecursing();

			if (isset($anchorClass)) {

				$reflectedCallable = new ReflectionMethod($anchorClass, $callable);

				if ($explicitCall) $this->setRecursingFor($anchorClass); // Assume class A wanna get parameters for class B->foo. When set to `lastCaller`, we'll be looking through class A's provisions instead of class B

				$context = $this->getRecursionContext();
			}

			else $reflectedCallable = new ReflectionFunction($callable);

			if ($explicitCall) $this->unsetRecursingFor();

			return $this->populateDependencies($reflectedCallable, $context);
		}

		private function populateDependencies (ReflectionFunctionAbstract $callable, ?ProvisionUnit $callerProvision):array {

			$dependencies = [];

			foreach ($reflectedCallable->getParameters() as $parameter) {

				$parameterName = $parameter->getName();

				$parameterType = $parameter->getType();

				if (!is_null($callerProvision) )

					$dependencies[$parameterName] = $this->populateForProvided($callerProvision, $parameterType, $parameterName);

				elseif (is_null($parameterType)) // untyped

					$dependencies[$parameterName] = $this->getParameterValue($parameterType);
				
				elseif ($parameter->isOptional() )

					$dependencies[$parameterName] = $parameter->getDefaultValue();

				else $dependencies[$parameterName] = null;
			}

			return $dependencies;
		}

		/**
		 * Pulls out a provided instance of a dependency when present, or creates a fresh one
		 * 
		 * @return object matching type at given parameter
		*/
		private function populateForProvided (ProvisionUnit $callerProvision, ReflectionType $parameterType, string $parameterName) {

			if ($callerProvision->hasArgument($parameterName))

				return $callerProvision->getArgument($parameterName);

			$typeName = $parameterType->getName();

			if ($callerProvision->hasArgument($typeName))

				return $callerProvision->getArgument($typeName);

			return $this->getParameterValue($parameterType);
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
		private function getProvidedParent(string $class):?string {

			$allSuperiors = array_keys($this->provisionedClasses);

			$classSuperiors = class_parents($class, true) +class_implements($class, true);

			$providedParents = array_intersect($classSuperiors, $allSuperiors);

			if (empty($providedParents)) return null;

			return current($providedParents);
		}

		public function whenSpace(string $callerNamespace):self {

			if (!array_key_exists($callerNamespace, $this->provisionedNamespaces))

				$this->provisionedNamespaces[$callerNamespace] = [];

			$this->provisionSpace = $callerNamespace;

			return $this;
		}

		public function renameServiceSpace(NamespaceUnit $unit):self {
			
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

		private function getLaravelHydrator ():LaravelService {

			if (is_null($this->laravelHydrator))

				$this->laravelHydrator = $this->instantiateConcrete(LaravelService::class);

			return $this->laravelHydrator;
		}

		private function unsetRecursingFor ():void {

			$this->recursingFor = null; // ahead of future invocations by other callers to modular Container
		}

		private function notRecursing ():bool {

			return is_null($this->recursingFor);
		}

		public function provideSelf ():void {

			$this->whenTypeAny()->needsAny([get_class() => $this]);
		}
	}
?>