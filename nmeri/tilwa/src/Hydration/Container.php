<?php
	namespace Tilwa\Hydration;

	use ReflectionMethod, ReflectionClass, ReflectionFunction, ReflectionType, ReflectionFunctionAbstract, Exception;
	
	use Tilwa\Hydration\Structures\{ProvisionUnit, NamespaceUnit, HydratedConcrete};
	
	use Tilwa\Hydration\Templates\CircularBreaker;

	use Tilwa\Exception\Explosives\Generic\InvalidImplementor;

	class Container {

		const UNIVERSAL_SELECTOR = "*";

		private $provisionedNamespaces = [], // NamespaceUnit[]

		$dependencyChain = [],

		$hydratingForStack = [], // String[] The last item is the one whose context/ProvisionUnit will be used in hydrating dependencies

		$internalMethodHydrate = false, // Used when [getMethodParameters] is called directly without going through instance methods such as [instantiateConcrete]

		$constructor = "__construct",

		$laravelHydrator, $interfaceHydrator,

		$provisionContext, // the active Type before calling `needs`

		$provisionSpace; // same as above, but for namespaces

		protected $provisionedClasses = []; // ProvisionUnit[]

		public function __construct () {

			$this->initializeUniversalProvision();
		}

		public function initializeUniversalProvision ():void {

			$this->provisionedClasses[self::UNIVERSAL_SELECTOR] = new ProvisionUnit;
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
		*	@param {includeSub} Regular provision: A wants B, but we give C sub-class of B. Sub-classes of A can't obtain B unless this parameter is used
		* 
		* @return A class instance, if found
		*/
		public function getClass (string $fullName, bool $includeSub = false) {

			$concrete = $this->decorateProvidedConcrete($fullName);

			if (!is_null($concrete)) return $concrete;

			if ($includeSub && $parent = $this->hydrateChildsParent($fullName))

				return $parent;

			$concrete = $this->loadLaravelLibrary($fullName);

			if (!is_null($concrete)) return $concrete;

			$reflectedClass = new ReflectionClass($fullName);

			if ($reflectedClass->isInterface())

				$concrete = $this->provideInterface($fullName);

			else if ($reflectedClass->isInstantiable())

				$concrete = $this->instantiateConcrete($fullName);

			return $concrete;
		}

		public function getProvidedConcrete (string $fullName) {

			$context = $this->getRecursionContext();

			if ($context->hasConcrete($fullName))

				return $context->getConcrete($fullName);
		}

		public function decorateProvidedConcrete (string $fullName) {

			$freshlyCreated = $this->hydratingForAction($fullName, function ($className) {

				$trueCaller = $this->lastHydratedFor(); // get this before it's overwritten by [getProvidedConcrete] cuz it has no provision
				return new HydratedConcrete($this->getProvidedConcrete($className), $trueCaller);
			});

			if (!is_null($freshlyCreated->getConcrete()))

				return $this->getDecorator()->scopeInjecting(
					$freshlyCreated->getConcrete(),

					$freshlyCreated->getCreatedFor()
				); // decorator runs on each fetch (rather than only once), since different callers result in different behavior
		}

		/**
		 * Updates the last element in the context hydrating stack, to that whose provision dependencies should be hydrated for
		*/
		protected function pushHydratingFor (string $fullName):void {

			$this->hydratingForStack[] = $fullName;
		}

		/**
		 * If we're hydrating dependency B of class A, we want to get provisions for B--not A, the last called; otherwise, we'll be looking through class A's provisions instead of class B
		*/
		protected function initializeHydratingFor (string $fullName):void {

			$isFirstCall = empty($this->hydratingForStack);

			if ($isFirstCall)

				$this->hydratingForStack[] = $this->lastCaller();

			else $this->hydratingForStack[] = $fullName;
		}

		/**
		 * Does not decorate objects since we can't have access to decorate those interfaces/entities. Plus, this method is a decorator on its own
		*/
		protected function loadLaravelLibrary( string $fullName ) {

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

		/**
		 * Not explicitly decorating objects from here since it calls [getClass]
		*/
		private function hydrateChildsParent (string $fullName) {

			$providedParent = $this->getProvidedParent($fullName);

			if (!is_null($providedParent))

				return $this->getClass($providedParent);
		}

		/**
		 * A shorter version of [getClass], but neither checks in cache or contextual provisions. This means they're useful to:
		 * 1) To hydrate classes we're sure doesn't exist in the cache
		 * 2) In methods that won't be called more than once in the request cycle
		 * 3) To create objects that are more or less static, or can't be overidden by an extension
		 * 
		 *  All objects internally derived from this trigger decorators if any are applied
		*/
		public function instantiateConcrete (string $fullName) {

			if (!method_exists($fullName, $this->constructor))

				$freshlyCreated = new HydratedConcrete(new $fullName, $this->lastHydratedFor());

			else $freshlyCreated = $this->hydratingForAction(
					
				$fullName, function ($className) {

					return $this->hydrateConcreteForCaller($className);
				}
			);

			$this->storeConcrete($fullName, $freshlyCreated->getConcrete());

			return $this->getDecorator()->scopeInjecting(
				$freshlyCreated->getConcrete(),

				$freshlyCreated->getCreatedFor()
			);
		}

		public function hydrateConcreteForCaller (string $className):HydratedConcrete {

			$this->dependencyChain[] = $className;

			$dependencies = $this->internalMethodGetParameters(function () use ($className) {

				return array_values($this->getMethodParameters($this->constructor, $className));
			});

			$this->unchainDependency($className);

			return new HydratedConcrete(
				new $className (...$dependencies),

				$this->lastHydratedFor()
			);
		}

		public function internalMethodGetParameters (callable $action) {

			$this->internalMethodHydrate = true;

			$result = $action();

			$this->internalMethodHydrate = false;

			return $result;
		}

		public function hydratingForAction (string $className, callable $action) {

			$this->pushHydratingFor($className);

			$result = $action($className);

			$this->popHydratingFor();

			return $result;
		}

		private function storeConcrete (string $fullName, $concrete):ProvisionUnit {

			return $this->getRecursionContext()->addConcrete($fullName, $concrete);
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
		public function getRecursionContext ():ProvisionUnit {

			$hydrateFor = $this->lastHydratedFor();

			if (!array_key_exists($hydrateFor, $this->provisionedClasses)) {

				$this->popHydratingFor();

				$this->pushHydratingFor(self::UNIVERSAL_SELECTOR);

				$hydrateFor = self::UNIVERSAL_SELECTOR;
			}

			return $this->provisionedClasses[$hydrateFor];
		}

		/**
		 * This tells us the class we just hydrated arguments for
		*/
		public function lastHydratedFor ():?string {

			if(empty($this->hydratingForStack) )

				return null;
			
			return end($this->hydratingForStack);
		}

		/**
		 * @throws Exception
		 * 
		 * @return Concrete of the given [Interface] if it has been provided
		*/
		private function provideInterface (string $interface) {

			return $this->hydratingForAction($interface, function ($className) use ($interface) {

				$caller = $this->lastHydratedFor();

				if ($this->hasRenamedSpace($caller)) {

					$newIdentity = $this->relocateSpace($interface, $caller);

					$concrete = $this->instantiateConcrete($newIdentity);
				}

				else {

					$concrete = $this->interfaceHydrator->deriveConcrete($interface);

					if (!is_null($concrete))

						$this->saveWhenImplements($fullName, $concrete);
				}

				if (is_null($concrete))

					throw new Exception("No matching concrete found for interface " . $interface);

				return $concrete;
			});
		}

		public function whenType (string $toProvision):self {

			if (!array_key_exists($toProvision, $this->provisionedClasses))

				$this->provisionedClasses[$toProvision] = new ProvisionUnit;

			$this->provisionContext = $toProvision;

			return $this;
		}

		public function whenTypeAny ():self {

			return $this->whenType(self::UNIVERSAL_SELECTOR);
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
		* 
		* @param {callable}:string|Closure
		* @param {anchorClass} the class the given method belongs to
		* 
		* @return {Array} associative. Contains hydrated parameters to invoke given callable with
		*/ 
		public function getMethodParameters ( $callable, string $anchorClass = null):array {

			$context = null;

			if (is_null($anchorClass))

				$reflectedCallable = new ReflectionFunction($callable);

			else {

				$reflectedCallable = new ReflectionMethod($anchorClass, $callable);

				if (!$this->internalMethodHydrate)

					$this->initializeHydratingFor($anchorClass);

				$context = $this->getRecursionContext();
			}

			$dependencies = $this->populateDependencies($reflectedCallable, $context);

			if (is_null($anchorClass)) return $dependencies;

			elseif (!$this->internalMethodHydrate)

				$this->popHydratingFor();

			return $this->getDecorator()->scopeArguments( $anchorClass, $dependencies, $callable);
		}

		public function populateDependencies (ReflectionFunctionAbstract $reflectedCallable, ?ProvisionUnit $callerProvision):array {

			$dependencies = [];

			foreach ($reflectedCallable->getParameters() as $parameter) {

				$parameterName = $parameter->getName();

				$parameterType = $parameter->getType();

				if (!is_null($callerProvision) )

					$dependencies[$parameterName] = $this->hydrateProvidedParameter($callerProvision, $parameterType, $parameterName);

				elseif (is_null($parameterType)) // untyped

					$dependencies[$parameterName] = $this->hydrateUnprovidedParameter($parameterType);
				
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
		private function hydrateProvidedParameter (ProvisionUnit $callerProvision, ReflectionType $parameterType, string $parameterName) {

			if ($callerProvision->hasArgument($parameterName))

				return $callerProvision->getArgument($parameterName);

			$typeName = $parameterType->getName();

			if ($callerProvision->hasArgument($typeName))

				return $callerProvision->getArgument($typeName);

			return $this->hydrateUnprovidedParameter($parameterType);
		}

		private function hydrateUnprovidedParameter (ReflectionType $parameterType) {

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

		/**
		 * @return the first provided parent of the given class
		*/
		private function getProvidedParent (string $class):?string {

			$allSuperiors = array_keys($this->provisionedClasses);

			$classSuperiors = array_merge(
				class_parents($class, true),

				class_implements($class, true)
			);

			return current(
				array_intersect($classSuperiors, $allSuperiors)
			);
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

		private function hasRenamedSpace (string $caller):bool {

			return array_key_exists($this->getNamespace($caller), $this->provisionedNamespaces);
		}

		private function relocateSpace (string $dependency, string $caller):string {

			$callerSpace = $this->getNamespace($caller);
			
			$dependencySpace = $this->getNamespace($dependency);

			foreach ($this->provisionedNamespaces[$callerSpace] as $spaceUnit)
				
				if ($spaceUnit->getSource() == $dependencySpace) {

					$newIdentity = $spaceUnit->getNewName(
						end(explode("\\", $dependency))
					);

					return $spaceUnit->getLocation() . "\\". $newIdentity;
				}
		}

		public function getNamespace (string $entityName):string {
			
			return (new ReflectionClass($entityName))->getNamespaceName();
		}

		/**
		*	@return Result of evaluating {constructor}
		*/
		public function genericFactory (string $generic, array $types, callable $constructor) {

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

		private function getLaravelHydrator ():LaravelProviderManager {

			if (is_null($this->laravelHydrator))

				$this->laravelHydrator = $this->instantiateConcrete(LaravelProviderManager::class);

			return $this->laravelHydrator;
		}

		/**
		 * Ahead of future invocations by other callers for provided objects
		*/
		private function popHydratingFor ():void {

			array_pop($this->hydratingForStack);
		}

		public function provideSelf ():void {

			$this->whenTypeAny()->needsAny([get_class() => $this]);
		}

		public function interiorDecorate ():void {

			$this->decorator = $this->instantiateConcrete(DecoratorHydrator::class);

			$this->decorator->assignScopes();
		}

		protected function getDecorator ():DecoratorHydrator {

			return $this->decorator;
		}
	}
?>