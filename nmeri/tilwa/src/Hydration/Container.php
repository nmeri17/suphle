<?php
	namespace Tilwa\Hydration;
	
	use Tilwa\Hydration\Structures\{ProvisionUnit, NamespaceUnit, HydratedConcrete};

	use Tilwa\Contracts\Hydration\ClassHydrationBehavior;

	use Tilwa\Exception\Explosives\Generic\{InvalidImplementor, HydrationException};

	use ReflectionMethod, ReflectionClass, ReflectionFunction, ReflectionType, ReflectionFunctionAbstract, ReflectionException;

	class Container {

		const UNIVERSAL_SELECTOR = "*",

		CLASS_CONSTRUCTOR = "__construct";

		private $provisionedNamespaces = [], // NamespaceUnit[]

		$hydratingForStack = [], // String[]. Doubles as a dependency chain. @see [lastHydratedFor] for main usage

		$internalMethodHydrate = false, // Used when [getMethodParameters] is called directly without going through instance methods such as [instantiateConcrete]

		$hydratingArguments = false,

		$externalHydrators = [], $externalContainerManager,

		$hydratedClassConsumers = [],

		$interfaceHydrator, $decorator,

		$provisionContext, // the active Type before calling `needs`

		$provisionSpace; // same as above, but for namespaces

		protected $provisionedClasses = []; // ProvisionUnit[]

		public function __construct () {

			$this->initializeUniversalProvision();
		}

		public function initializeUniversalProvision ():void {

			$this->provisionedClasses[self::UNIVERSAL_SELECTOR] = new ProvisionUnit;
		}

		/**
		 * @param {externalHydrators} string<ExternalPackageManager>[]
		*/
		public function setExternalHydrators (array $externalHydrators):void {

			$this->externalHydrators = $externalHydrators;
		}
	
		/**
		 * Should be called when preparing container for use i.e. before the very first user facing getClass
		*/
		public function setExternalContainerManager ():void {

			if (!empty($this->externalHydrators)) {

				$this->externalContainerManager = new ExternalPackageManagerHydrator($this);

				$this->externalContainerManager->setManagers($this->externalHydrators);
			}
		}

		public function getExternalContainerManager ():?ExternalPackageManagerHydrator {

			return $this->externalContainerManager;
		}

		public function setInterfaceHydrator (string $collection):void {

			$concrete = $this->instantiateConcrete($collection);

			$this->interfaceHydrator = new InterfaceHydrator($concrete, $this);
		}

		public function getInterfaceHydrator ():InterfaceHydrator {

			return $this->interfaceHydrator;
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

			$externalManager = $this->externalContainerManager;

			if (
				!is_null($externalManager) &&

				$concrete = $externalManager->findInManagers($fullName)
			) {

				$this->saveWhenImplements($fullName, $concrete);

				return $concrete;
			}

			return $this->initializeHydratingForAction($fullName, function ($className) {

				if ($this->getReflectedClass($className)->isInterface())

					return $this->provideInterface($className);

				return $this->instantiateConcrete($className);
			});
		}

		public function decorateProvidedConcrete (string $fullName) {

			$freshlyCreated = $this->initializeHydratingForAction($fullName, function ($className) {

				return new HydratedConcrete(
					$this->getProvidedConcrete($className),

					$this->lastHydratedFor()
				);
			});

			$concrete = $freshlyCreated->getConcrete();

			if (!is_null($concrete)) {

				$decorator = $this->getDecorator();

				return $decorator ? $decorator->scopeInjecting( // decorator runs on each fetch (rather than only once), since different callers result in different behavior
					
					$concrete, $freshlyCreated->getCreatedFor()
				): $concrete;
			}
		}

		public function getProvidedConcrete (string $fullName) {

			$context = $this->getRecursionContext();

			if (!$context->hasConcrete($fullName)) // current provision doesn't include this class. check in global

				$context = $this->provisionedClasses[self::UNIVERSAL_SELECTOR];

			if ($context->hasConcrete($fullName)) {

				$this->setConsumer($fullName);

				return $context->getConcrete($fullName);
			}
		}

		/**
		* Switches unit being provided to universal if it doesn't exist
		* @return currently available provision unit
		*/
		public function getRecursionContext ():ProvisionUnit {

			$hydrateFor = $this->lastHydratedFor();

			if (!array_key_exists($hydrateFor, $this->provisionedClasses))

				$hydrateFor = self::UNIVERSAL_SELECTOR;

			return $this->provisionedClasses[$hydrateFor];
		}

		/**
		 * This tells us the class we are hydrating arguments for
		*/
		public function lastHydratedFor ():?string {

			$stack = $this->hydratingForStack;

			if(empty($stack) ) return null;

			$index = $this->hydratingArguments ? 2: 1; // If we're hydrating class A -> B -> C, we want to get provisions for B (who, at this point, is indexed -2 while C is -1). otherwise, we'll be looking through C's provisions instead of B

			$length = count($stack);

			return $stack[$length - $index];
		}

		private function setConsumer (string $fullName):void {

			if (!array_key_exists($fullName, $this->hydratedClassConsumers))

				$this->hydratedClassConsumers[$fullName] = [];

			$concreteHydratedFor = $this->lastHydratedFor();

			if ($concreteHydratedFor != $fullName)

				$this->hydratedClassConsumers[$fullName][] = $concreteHydratedFor;
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

		private function saveWhenImplements (string $interface, $concrete):void {

			if (!($concrete instanceof $interface))

				throw new InvalidImplementor($interface, get_class($concrete));

			$this->storeConcrete( $interface, $concrete);

			$this->setConsumer($interface);
		}

		private function storeConcrete (string $fullName, $concrete):ProvisionUnit {

			return $this->getRecursionContext()->addConcrete($fullName, $concrete);
		}

		private function getReflectedClass (string $className):ReflectionClass {

			try {

				return new ReflectionClass($className);
			}
			catch (ReflectionException $re) {

				$message = "Unable to hydrate ". $this->lastHydratedFor() . ": ". $re->getMessage();

				$hint = "Hint: Cross-check its dependencies";

				throw new HydrationException("$message. $hint");
			}
		}

		/**
		 * Wrap any call that internally attempts to read from [lastHydratedFor] in this i.e. calls that do some hydration and need to know what context/provision they're being hydrated for
		*/
		public function initializeHydratingForAction (string $fullName, callable $action) {

			$this->initializeHydratingFor($fullName);

			$result = $action($fullName);

			$this->popHydratingFor($fullName);

			return $result;
		}

		/**
		 * Tells us who to hydrate arguments for
		*/
		protected function initializeHydratingFor (string $fullName):void {

			$isFirstCall = is_null($this->lastHydratedFor());

			$hydrateFor = $isFirstCall ? $this->lastCaller(): $fullName;

			$this->pushHydratingFor($hydrateFor);
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
		 * Updates the last element in the context hydrating stack, to that whose provision dependencies should be hydrated for
		*/
		protected function pushHydratingFor (string $fullName):void {

			$this->hydratingForStack[] = $fullName;
		}

		/**
		 * @param {completedHydration} To guarantee push-pop consistency. When the name of what is expected to be removed doesn't match the last item in stack, it indicates we're currently hydrating an interface (where its name differs from concretes involved). When this happens, we simply ignore popping our list since those concretes were not the ones that originally got pushed
		*/
		private function popHydratingFor (string $completedHydration):void {

			if (end($this->hydratingForStack) == $completedHydration)

				array_pop($this->hydratingForStack);
		}

		/**
		 * @throws InvalidImplementor
		 * 
		 * @return Concrete of the given [Interface] if it was bound
		*/
		protected function provideInterface (string $interface) {

			$caller = $this->lastHydratedFor();

			if ($this->hasRenamedSpace($caller)) {

				$newIdentity = $this->relocateSpace($interface, $caller);

				$concrete = $this->instantiateConcrete($newIdentity);
			}

			else {

				$concrete = $this->getInterfaceHydrator()->deriveConcrete($interface);

				if (!is_null($concrete))

					$this->saveWhenImplements($interface, $concrete);
			}

			if (is_null($concrete))

				throw new InvalidImplementor($interface, "No matching concrete" );

			return $concrete;
		}

		private function hasRenamedSpace (string $caller):bool {

			return array_key_exists($this->classNamespace($caller), $this->provisionedNamespaces);
		}

		private function relocateSpace (string $dependency, string $caller):string {

			$callerSpace = $this->classNamespace($caller);
			
			$dependencySpace = $this->classNamespace($dependency);

			foreach ($this->provisionedNamespaces[$callerSpace] as $spaceUnit)
				
				if ($spaceUnit->getSource() == $dependencySpace) {

					$newIdentity = $spaceUnit->getNewName(
						@end(explode("\\", $dependency))
					);

					return $spaceUnit->getLocation() . "\\". $newIdentity;
				}
		}

		public function classNamespace (string $entityName):string {
			
			return (new ReflectionClass($entityName))->getNamespaceName();
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

			$freshlyCreated = $this->initializeHydratingForAction ($fullName, function ($className) {

				if (!method_exists($className, self::CLASS_CONSTRUCTOR)) // note that this throws a fatal, uncatchable error when class is in an unparseable state like missing abstract method or contract implementation

					return new HydratedConcrete(new $className, $this->lastHydratedFor() );

				return $this->hydrateConcreteForCaller($className);
			});

			$concrete = $freshlyCreated->getConcrete();

			$decorator = $this->getDecorator();

			$this->storeConcrete($fullName, $concrete);

			return $decorator ? $decorator->scopeInjecting(
				
				$concrete, $freshlyCreated->getCreatedFor()
			): $concrete;
		}

		public function hydrateConcreteForCaller (string $className):HydratedConcrete {

			$dependencies = $this->internalMethodGetParameters(function () use ($className) {

				return array_values($this->getMethodParameters(self::CLASS_CONSTRUCTOR, $className));
			});

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

		/**
		*	Fetch appropriate dependencies for a callable's arguments
		* 
		* @param {callable}:string|Closure
		* @param {anchorClass} the class the given method belongs to
		* 
		* @return {Array} associative. Contains hydrated parameters to invoke given callable with
		* 
		* @throws ReflectionException if method doesn't exist on class
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

				$this->popHydratingFor($anchorClass);

			$decorator = $this->getDecorator();

			return $decorator ? $decorator->scopeArguments( $anchorClass, $dependencies, $callable): $dependencies;
		}

		public function populateDependencies (ReflectionFunctionAbstract $reflectedCallable, ?ProvisionUnit $callerProvision):array {

			$dependencies = [];
			
			$callerIsClosure = $reflectedCallable->isClosure();

			foreach ($reflectedCallable->getParameters() as $parameter) {

				$parameterName = $parameter->getName();

				$parameterType = $parameter->getType();

				if (!is_null($callerProvision) )

					$dependencies[$parameterName] = $this->hydrateProvidedParameter($callerProvision, $parameterType, $parameterName, $callerIsClosure);

				elseif (!is_null($parameterType))

					$dependencies[$parameterName] = $this->hydrateUnprovidedParameter($parameterType, $callerIsClosure);
				
				elseif ($parameter->isOptional() )

					$dependencies[$parameterName] = $parameter->getDefaultValue();

				else $dependencies[$parameterName] = null;
			}

			return $dependencies;
		}

		/**
		 * Pulls out a provided instance of a dependency when present, or creates a fresh one
		 * 
		 * @param {parameterType} Null for parameters that weren't typed
		 * 
		 * @return mixed. Entity matching type at given parameter
		*/
		private function hydrateProvidedParameter (ProvisionUnit $callerProvision, ?ReflectionType $parameterType, string $parameterName, bool $callerIsClosure) {

			if ($callerProvision->hasArgument($parameterName))

				return $callerProvision->getArgument($parameterName);

			if (is_null($parameterType)) return null;
				
			$typeName = $parameterType->getName();

			if ($callerProvision->hasArgument($typeName))

				return $callerProvision->getArgument($typeName);

			return $this->hydrateUnprovidedParameter($parameterType, $callerIsClosure);
		}

		private function hydrateUnprovidedParameter (ReflectionType $parameterType, bool $callerIsClosure) {

			$typeName = $parameterType->getName();

			if ( $parameterType->isBuiltin()) {

				$defaultValue = null;

				settype($defaultValue, $typeName);

				return $defaultValue;
			}

			if (!in_array($typeName, $this->hydratingForStack)) {

				if ($callerIsClosure)

					$concrete = $this->getClass($typeName); // there's no extra layer of called scope->given object (to get arguments for). We only have called scope in the stack; so, get immediate last item

				else {

					$this->hydratingArguments = true;

					$concrete = $this->getClass($typeName);

					$this->hydratingArguments = false;
				}

				return $concrete;
			}

			if ($this->getReflectedClass($typeName)->isInterface())

				throw new HydrationException ("$typeName's concrete cannot depend on its dependency's concrete");

			$classNameArray = range("a", "z");

			shuffle($classNameArray);

			$newClassName = substr(implode("", $classNameArray), 3, 20);

			trigger_error("Circular dependency detected while hydrating $typeName", E_USER_WARNING);

			return $this->genericFactory(
				__DIR__ . DIRECTORY_SEPARATOR . "Templates" . DIRECTORY_SEPARATOR . "CircularBreaker.php", 

				[
					"className" => $newClassName,

					"target" => $typeName,

					"extends" => /*$isInterface ? "implements":*/ "extends"
				],

				function ($types) use ($newClassName) {

			    	return new $newClassName($types["target"], $this);
				}
			); // A requests B and vice versa. If A makes the first call, we're returning a proxied/fake A to the B instance we pass to the real A
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

				throw new HydrationException("Undefined provisionContext");

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

				throw new HydrationException("Undefined provisionContext");

			$this->provisionedClasses[$this->provisionContext]->updateArguments($argumentList);
			
			return $this;
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

		/**
		*	@return Result of evaluating {constructor}
		*/
		public function genericFactory (string $classPath, array $types, callable $constructor) {

			$genericContents = file_get_contents($classPath);

		    foreach ($types as $placeholder => $type)

		        $genericContents = str_replace("<$placeholder>", $type, $genericContents);

		    eval($genericContents);

		    return $constructor($types);
		}

		public function provideSelf ():void {

			$this->whenTypeAny()->needsAny([get_class() => $this]);
		}

		public function interiorDecorate ():void {

			$this->decorator = $this->instantiateConcrete(DecoratorHydrator::class);

			$this->decorator->assignScopes();
		}

		/**
		 * Since this isn't injected, we're using this as inlet to stub out decorator when desired
		 * 
		 * @return null when we're either hydrating interfaceCollection or the decorator itself
		*/
		protected function getDecorator ():?DecoratorHydrator {

			return $this->decorator;
		}

		/**
		 * Note: Doesn't purge consumers that are interfaces (since we can't catch them), only their concretes
		*/
		public function refreshClass (string $className):void {

			foreach ($this->provisionedClasses as $provisionContext) {

				if ( $provisionContext->hasConcrete($className) || $provisionContext->hasArgument($className)) {

					$consumers = $this->hydratedClassConsumers;

					if (array_key_exists($className, $consumers))

						foreach ($consumers[$className] as $consumer)

							if (
								!in_array(ClassHydrationBehavior::class, class_implements($consumer)) ||

								!$this->getProvidedConcrete($consumer)->protectRefreshPurge($className)
							)

								$this->refreshClass($consumer);

					$provisionContext->purgeAll($className);
				}
			}
		}

		public function refreshMany (array $classes):void {

			foreach ($classes as $className)

				$this->refreshClass($className);
		}
	}
?>