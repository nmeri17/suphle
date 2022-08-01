<?php
	namespace Suphle\Hydration;
	
	use Suphle\Hydration\Structures\{ProvisionUnit, NamespaceUnit, HydratedConcrete, ObjectDetails, ContainerTelescope};

	use Suphle\Contracts\{Hydration\ClassHydrationBehavior, Config\ContainerConfig as IContainerConfig};

	use Suphle\Exception\Explosives\Generic\{InvalidImplementor, HydrationException};

	use ReflectionMethod, ReflectionFunction, ReflectionType, ReflectionFunctionAbstract, ReflectionException;

	class Container implements ClassHydrationBehavior {

		const UNIVERSAL_SELECTOR = "*",

		CLASS_CONSTRUCTOR = "__construct";

		private $provisionedNamespaces = [], // NamespaceUnit[]

		$hydratingForStack = [], // String[]. Doubles as a dependency chain. @see [lastHydratedFor] for main usage

		$internalMethodHydrate = [], // Used when [getMethodParameters] is called directly without going through instance methods such as [instantiateConcrete]

		$hydratingArguments = false,

		$config, $externalContainers,

		$hydratedClassConsumers = [],

		$interfaceHydrator, $decorator, $objectMeta,

		$provisionContext, // the active Type before calling needs

		$provisionSpace, // same as above, but for namespaces

		$telescope;

		protected $provisionedClasses = []; // ProvisionUnit[]

		public function __construct () {

			$this->initializeUniversalProvision();
		}

		public function initializeUniversalProvision ():void {

			$this->provisionedClasses[self::UNIVERSAL_SELECTOR] = new ProvisionUnit(self::UNIVERSAL_SELECTOR);
		}
	
		/**
		 * Should be called when preparing container for use i.e. before the very first user facing getClass
		*/
		public function setExternalContainerManager (ExternalPackageManagerHydrator $externalContainers):void {

			$externalContainers->setManagers(

				$this->config->getExternalHydrators()
			);

			$this->externalContainers = $externalContainers;
		}

		public function setInterfaceHydrator (string $collection):void {

			$concrete = $this->instantiateConcrete($collection);

			$this->interfaceHydrator = new InterfaceHydrator($concrete, $this);

			$this->config = $this->provideInterface(IContainerConfig::class); // setting config within the same method since it's impossible for config to be gotten if interface collection is absent
		}

		public function getInterfaceHydrator ():InterfaceHydrator {

			return $this->interfaceHydrator;
		}

		public function protectRefreshPurge ():bool {

			return true;
		}

		public function setTelescope (ContainerTelescope $telescope):void {

			$this->telescope = $telescope;
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

			$externalManager = $this->externalContainers;

			if (
				!is_null($externalManager) &&

				$concrete = $externalManager->findInManagers($fullName)
			) {

				$this->saveWhenImplements($fullName, $concrete);

				return $concrete;
			}

			return $this->initializeHydratingForAction($fullName, function ($className) {

				$this->setConsumer($className);

				if ($this->objectMeta->isInterface($className))

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

		public function getProvidedConcrete (string $fullName):?object {

			$contentOwner = $originalOwner = $this->lastHydratedFor(); // prevent premature switching by not using [getRecursionContext] since we don't know yet whether [activeProvision] has it

			$provisions = $this->provisionedClasses;

			if (is_null($originalOwner) ||

				!array_key_exists($originalOwner, $provisions)
			)

				$contentOwner = self::UNIVERSAL_SELECTOR;

			$activeProvision = $provisions[$contentOwner];

			if (!$activeProvision->hasConcrete($fullName) &&

				$contentOwner != self::UNIVERSAL_SELECTOR
			) // fallback

				$activeProvision = $provisions[self::UNIVERSAL_SELECTOR];

			if ($activeProvision->hasConcrete($fullName)) {

				$this->setConsumer($fullName, $originalOwner);

				if (!is_null($this->telescope))

					$this->telescope->addReadConcretes(

						$contentOwner, $fullName
					);

				return $activeProvision->getConcrete($fullName);
			}

			if (!is_null($this->telescope) && !is_null($originalOwner))

				$this->telescope->addMissingConcrete(

					$originalOwner, $fullName
				);

			return null;
		}

		/**
		* Switches unit being provided to universal if it doesn't exist
		* @return currently available provision unit
		*/
		public function getRecursionContext ():ProvisionUnit {

			$hydrateFor = $this->lastHydratedFor();

			$provisions = $this->provisionedClasses;

			if (is_null($hydrateFor))

				return $provisions[self::UNIVERSAL_SELECTOR];

			if (!array_key_exists($hydrateFor, $provisions) ) {

				if (!is_null($this->telescope))

					$this->telescope->addMissingContext($hydrateFor);

				$hydrateFor = self::UNIVERSAL_SELECTOR;
			}

			return $provisions[$hydrateFor];
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

		/**
		 * Records a class Dependent under each of its dependencies
		 * @param {fullname} Dependency
		 * @param {missingProvision} When present, we don't infer class Dependent but use this explicitly
		*/
		private function setConsumer (string $fullName, string $missingProvision = null):void {

			$concreteHydratedFor = $missingProvision ?? $this->lastHydratedFor();

			if (is_null($concreteHydratedFor)) return;

			if (!array_key_exists($fullName, $this->hydratedClassConsumers))

				$this->hydratedClassConsumers[$fullName] = [];

			$isNotInterfaceLoading = $concreteHydratedFor != $fullName && 

				!$this->objectMeta->stringInClassTree(

					$fullName, $concreteHydratedFor
				); // prevent recursive loop during purge

			$isNotConsumedAlready = !in_array(
					$concreteHydratedFor,

					$this->hydratedClassConsumers[$fullName]
				);

			if ($isNotInterfaceLoading && $isNotConsumedAlready ) {

				$this->hydratedClassConsumers[$fullName][] = $concreteHydratedFor;

				$this->dependOnConsumerParents($fullName, $concreteHydratedFor);

				if (!is_null($this->telescope))

					$this->telescope->setConsumerList($this->hydratedClassConsumers);
			}
		}

		/**
		 * [dependent] has [dependency] as a dependency in its constructor, usually, the interface version of [dependency]
		*/
		private function dependOnConsumerParents (string $dependency, string $dependent):void {

			foreach (array_unique($this->hydratingForStack) as $potentialParent) {

				$isParent = $potentialParent != $dependent &&

				$this->objectMeta->stringInClassTree(

					$dependent, $potentialParent
				);

				if (!$isParent) continue;

				$this->hydratedClassConsumers[$dependency][] = $potentialParent;

				if (!is_null($this->telescope))

					$this->telescope->addConsumerParent($potentialParent, $dependency);
			}
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
		}

		private function storeConcrete (string $fullName, $concrete):ProvisionUnit {

			$callerProvision = $this->getRecursionContext();
			
			if (!is_null($this->telescope))

				$this->telescope->addStoredConcrete(

					$callerProvision->getOwner(), $fullName
				);

			return $callerProvision->addConcrete($fullName, $concrete);
		}

		/**
		 * Wrap any call that internally attempts to read from [lastHydratedFor] in this i.e. calls that do some hydration and need to know what context/provision they're being hydrated for
		*/
		public function initializeHydratingForAction (string $fullName, callable $action) {

			$pushedItems = $this->initializeHydratingFor($fullName);

			$result = $action($fullName);

			foreach ($pushedItems as $entity)

				$this->popHydratingFor($entity);

			return $result;
		}

		/**
		 * Decides who to hydrate arguments for
		 * 
		 * @return its decision, so it can be used for popping after performing desired actions
		*/
		protected function initializeHydratingFor (string $fullName):array {

			$dependent = $this->lastHydratedFor();

			$isFirstCall = is_null($dependent);

			if ( $isFirstCall) $hydrateFor = $this->lastCaller();

			else $hydrateFor = $dependent;

			$this->pushHydratingFor($hydrateFor);

			$this->pushHydratingFor($fullName); // pushing both caller and class being hydrated so lastHydratedFor can read from either if need be

			return [$hydrateFor, $fullName];
		}

		private function lastCaller ():string {

			$stack = debug_backtrace (2 ); // 2=> ignore concrete objects and their args

			$caller = "class";

			foreach ($stack as $execution)

				if (array_key_exists($caller, $execution) && $execution[$caller] != get_class()) {

					return $execution[$caller];
				}

			return ""; // will be empty when container is called in isolation i.e. without being wrapped by the objects that precede user-land e.g. in the outermost index script handling requests
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

			$index = array_search($completedHydration, $this->hydratingForStack);

			if (isset($this->hydratingForStack[$index])) {

				unset($this->hydratingForStack[$index]);

				$this->hydratingForStack = array_values($this->hydratingForStack);
			}
		}

		/**
		 * @throws InvalidImplementor
		 * 
		 * @return Concrete of the given [Interface] if it was bound
		*/
		protected function provideInterface (string $interface) {

			$caller = $this->lastHydratedFor();

			if ($caller && $this->hasRenamedSpace($caller)) {

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

			return array_key_exists(
				$this->objectMeta->classNamespace($caller),

				$this->provisionedNamespaces
			);
		}

		private function relocateSpace (string $dependency, string $caller):string {

			$callerSpace = $this->objectMeta->classNamespace($caller);
			
			$dependencySpace = $this->objectMeta->classNamespace($dependency);

			foreach ($this->provisionedNamespaces[$callerSpace] as $spaceUnit)
				
				if ($spaceUnit->getSource() == $dependencySpace) {

					$newIdentity = $spaceUnit->getNewName(
						@end(explode("\\", $dependency))
					);

					return $spaceUnit->getLocation() . "\\". $newIdentity;
				}
		}

		/**
		 * In comparison to [getClass], this neither checks cache nor for contextual provisions. It assumes it's called within a hydration context that has already set appropriate scopes in place i.e. not in isolation
		 * 
		 *  All objects internally derived from this trigger decorators if any are applied
		*/
		public function instantiateConcrete (string $fullName):object {

			$decorator = $this->getDecorator();

			$freshlyCreated = $this->initializeHydratingForAction ($fullName, function ($className) use ($decorator) { // we need this double coating since we intend to read arguments later, and [lastHydratedFor] is expected to see a list of at least 2 items during argument reading

				if (!method_exists($className, self::CLASS_CONSTRUCTOR)) { // note that this throws a fatal, uncatchable error when class is in an unparseable state like missing abstract method or contract implementation

					if (!is_null($decorator))

						$decorator->scopeArguments( $className, [], self::CLASS_CONSTRUCTOR);

					return new HydratedConcrete(new $className, $this->lastHydratedFor() );
				}

				return $this->hydrateConcreteForCaller($className);
			});

			$concrete = $freshlyCreated->getConcrete();

			$this->storeConcrete($fullName, $concrete);

			return $decorator ? $decorator->scopeInjecting(
				
				$concrete, $freshlyCreated->getCreatedFor()
			): $concrete;
		}

		public function hydrateConcreteForCaller (string $className):HydratedConcrete {

			$currentArgumentState = $this->hydratingArguments;
			
			$this->hydratingArguments = false; // we want this class and below call to be treated as first class citizen i.e. lastHydratedFor should not yield provisions for argument mode (-2)
			
			$dependencies = array_values( $this->getMethodParameters(

				self::CLASS_CONSTRUCTOR, $className
			));

			$this->hydratingArguments = $currentArgumentState;

			return new HydratedConcrete(
				new $className (...$dependencies),

				$this->lastHydratedFor()
			);
		}

		public function internalMethodGetParameters (string $className, callable $action) {

			$this->internalMethodHydrate[] = $className;

			$this->hydratingArguments = true;

			$result = $action($className);

			unset($this->internalMethodHydrate[

				array_search($className, $this->internalMethodHydrate)
			]);

			$this->hydratingArguments = false;

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

			$pushedItems = [];

			if (is_null($anchorClass))

				$reflectedCallable = new ReflectionFunction($callable);

			else {

				$reflectedCallable = new ReflectionMethod($anchorClass, $callable);

				if (!$this->hydratingInternally($anchorClass))

					$pushedItems = $this->initializeHydratingFor($anchorClass);

				$context = $this->getRecursionContext();
			}

			$dependencies = $this->populateDependencies($reflectedCallable, $context);

			if (is_null($anchorClass)) return $dependencies;

			elseif (!$this->hydratingInternally($anchorClass))

				foreach ($pushedItems as $entity)

					$this->popHydratingFor($entity);

			$decorator = $this->getDecorator();

			return $decorator ? $decorator->scopeArguments( $anchorClass, $dependencies, $callable): $dependencies;
		}

		private function hydratingInternally (string $fullName):bool {

			return in_array($fullName, $this->internalMethodHydrate);
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

				$providedValue = $callerProvision->getArgument($parameterName);

			else if (is_null($parameterType)) return null;

			else {

				$typeName = $parameterType->getName();

				if ($callerProvision->hasArgument($typeName)) {

					$this->setConsumer($typeName);

					$providedValue = $callerProvision->getArgument($typeName);
				}
			}

			if ( isset($providedValue)) {

				if (!is_null($this->telescope) )

					$this->telescope->addReadArguments(

						$callerProvision->getOwner(), $parameterName, $providedValue
					);

				return $providedValue;
			}

			if (!is_null($this->telescope) )

				$this->telescope->addMissingArgument(

					$callerProvision->getOwner(), $parameterName
				);

			return $this->hydrateUnprovidedParameter($parameterType, $callerIsClosure);
		}

		/**
		 * @return mixed. Can be anything argument is typed to
		*/
		private function hydrateUnprovidedParameter (ReflectionType $parameterType, bool $callerIsClosure) {

			$typeName = $parameterType->getName();

			if ( $parameterType->isBuiltin())

				return $this->objectMeta->getScalarValue($typeName);

			if (!in_array($typeName, $this->hydratingForStack)
			) {

				if (!$callerIsClosure )

					$concrete = $this->internalMethodGetParameters($typeName, function ($className) {

						return $this->getClass($className);
					});

				else $concrete = $this->getClass($typeName); // there's no extra layer of called scope->given object (to get arguments for). We only have called scope in the stack; so, get immediate last item

				$this->setConsumer($typeName);

				return $concrete;
			}

			if ($this->objectMeta->isInterface($typeName))

				throw new HydrationException ("$typeName's concrete cannot depend on its dependency's concrete");

			$classNameArray = range("a", "z");

			shuffle($classNameArray);

			$newClassName = substr(implode("", $classNameArray), 3, 20);

			trigger_error(
				"Circular dependency detected. Hint: Compare $typeName and ".$this->lastHydratedFor(),

				E_USER_WARNING
			);

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

				$this->provisionedClasses[$toProvision] = new ProvisionUnit($toProvision);

			$this->provisionContext = $toProvision;

			return $this;
		}

		public function whenTypeAny ():self {

			return $this->whenType(self::UNIVERSAL_SELECTOR);
		}

		public function needs (array $dependencyList):self {

			if (is_null ($this->provisionContext))

				throw new HydrationException("Undefined provisionContext");

				if (!is_null($this->telescope))

					$this->telescope->addWrittenConcretes(
						$this->provisionContext,

						array_keys($dependencyList)
					);

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

				if (!is_null($this->telescope))

					$this->telescope->addWrittenArguments(
						$this->provisionContext,

						array_keys($argumentList)
					);

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

		public function setEssentials ():void {

			$this->provideSelf();

			$this->objectMeta = new ObjectDetails($this);
		}

		public function provideSelf ():void {

			$this->whenTypeAny()->needsAny([get_class() => $this]);
		}

		public function interiorDecorate ():void {

			$this->decorator = $this->getClass(DecoratorHydrator::class);

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

		public function refreshClass (string $className):void {

			foreach ($this->provisionedClasses as $provisionContext) {

				if (!$provisionContext->hasAnywhere($className) ||

					!$this->canClearClass($className)
				)

					continue;

				if (!is_null($this->telescope))

					$this->telescope->addRefreshedEntities($className);

				$provisionContext->clearInProvision($className);

				$consumers = $this->hydratedClassConsumers;

				if (!array_key_exists($className, $consumers)) continue;

				foreach (array_unique($consumers[ $className]) as $consumer)

					$this->refreshClass($consumer);
			}
		}

		private function canClearClass ( string $className):bool {

			return !$this->objectMeta->implementsInterface(

				$className, ClassHydrationBehavior::class
			) ||

			!$this->getProvidedConcrete($className)

			->protectRefreshPurge();
		}

		public function refreshMany (array $classes):void {

			foreach ($classes as $className)

				$this->refreshClass($className);
		}
	}
?>