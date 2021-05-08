<?php

	namespace Tilwa\App;

	use { ReflectionMethod, ReflectionClass, ReflectionFunction, ReflectionType};
	
	use Tilwa\App\Structures\{ProvisionUnit, NamespaceUnit};
	
	use Tilwa\App\Templates\CircularBreaker;

	class Container {

		private $provisionedClasses = [], // ProvisionUnit[]

		$serviceProviders = [],

		$provisionedNamespaces = [], // NamespaceUnit[]

		$dependencyChain = [],

		$provisionContext, // the active Type before calling `needs`

		$provisionSpace,

		$recursingFor; // the active ProvisionUnit

		public function setServiceProviders (array $providers):self {

			$this->serviceProviders = $providers;

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
		public function getClass (string $fullName, bool $includeSub = false):object {

			if (is_null($this->recursingFor))

				$this->recursingFor = $this->lastCaller();

			else $this->recursingFor = $fullName;

			$context = $this->getRecursionContext();

			if ($context->hasConcrete($this->recursingFor))

				return $concrete->getConcrete($this->recursingFor);

			if ($includeSub) {

				$providedParent = $this->getProvidedParent($fullName);

				if ($providedParent)

					return $this->getClass($providedParent);
			}

			$reflectedClass = new ReflectionClass($fullName);

			if ($reflectedClass->isInterface())

				return $this->provideInterface($fullName);

			if ($reflectedClass->isInstantiable()) {

				$this->dependencyChain[] = $fullName;
				
				$dependencies = $this->getMethodParameters("__construct", $fullName);

				$concrete = new $fullName (...$dependencies);

				$this->provisionedClasses[$this->recursingFor]

				->addConcrete($fullName, $concrete);

				return $concrete;
			}
		}

		private function lastCaller ():string {

			// 2=> ignore concrete objects and their args
			$stack = debug_backtrace ( 2, 3 ); // [lastCaller,getClass,ourGuy]. A more extensible (we won't have to specify limit anymore) but memory intensive way to go about this is to remove the limit and group them by class. then pick the immediate last one that doesn't correspond to `self::class`

			return end($stack)["class"];
		}

		/**
		* Switches unit being provided to universal if it doesn't exist
		* @return currently available provision unit
		*/
		private function getRecursionContext():ProvisionUnit {

			if (!array_key_exists($this->recursingFor, $this->provisionedClasses))

				$this->recursingFor = "*";

			return $this->provisionedClasses[$this->recursingFor];
		}

		private function provideInterface(string $service):object {

			if ($this->isRenamedSpace())

				return $this->relocateSpace($service);

			$providerClass = $this->serviceProviders[$service];

			$provider = new $providerClass();

			$providerArguments = $this->getMethodParameters("bindArguments", $providerClass);

			$providerParameters = call_user_func_array([$provider, "bindArguments"], $providerArguments);

			$concrete = $provider->concrete();

			$this->whenType($concrete) // for easy instantiation by getClass

			->needsArguments($providerParameters);
				
			$reflectedClass = $this->getClass($concrete);

			$provider->afterBind($reflectedClass);

			$this->whenTypeAny()

			->needsAny([$service => $reflectedClass]);

			return $reflectedClass;
		}

		public function whenType (string $toProvision):self {

			if (!array_key_exists($toProvision, $this->provisionedClasses))

				$this->provisionedClasses[$toProvision] = new ProvisionUnit;

			$this->provisionContext = $toProvision;

			return $this;
		}

		public function whenTypeAny ():self {

			return $this->whenType("*");
		}

		public function needs (array $dependencyList):self {

			$this->provisionedClasses[$this->provisionContext]->updateConcretes($dependencyList);
			
			return $this;
		}

		public function needsAny (array $dependencyList):self {

			$this->provisionedClasses[$this->provisionContext]->updateConcretes($dependencyList);

			$this->provisionedClasses[$this->provisionContext]->updateArguments($dependencyList);
			
			return $this;
		}

		public function needsArguments (array $argumentList):self {

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

			if (isset($anchorClass)) {

				$reflectedCallable = new ReflectionMethod($anchorClass, $callable);

				if (is_null($this->recursingFor))

					$this->recursingFor = $anchorClass; // Assume class A wanna get parameters for class B->foo. When set to `lastCaller`, we'll be looking through class A's provisions instead of class B

				$context = $this->getRecursionContext();
			}
			else $reflectedCallable = new ReflectionFunction($callable);

			foreach ($reflectedCallable->getParameters() as $parameter) {

				$parameterName = $parameter->getName();

				if ($context && $context->hasArgument($parameterName))

					$dependencies[$parameterName] = $context->getArgument($parameterName]);

				if ($parameter->hasType())

					$dependencies[$parameterName] = $this->getParameterValue($parameter->getType());
				
				elseif ($parameter->isOptional() )

					$dependencies[$parameterName] = $parameter->getDefaultValue();

				else $dependencies[$parameterName] = null;
			}
			return $dependencies;
		}

		private function getParameterValue(ReflectionType $parameterType) {

			$typeName = $parameterType->getName();

			if ( !$parameterType->isBuiltin()) {

				if (!in_array($typeName, $this->dependencyChain))

					return $this->getClass($typeName);

				return $this->breakCircular($typeName); // A requests B and vice versa. If A makes the first call, we're returning a proxied/fake A to the B instance we pass to the real A
			}

			$defaultValue = null;

			settype($defaultValue, $typeName);

			return $defaultValue;
		}

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

		/**
		*	@return Concrete instance of the requested namespace
		*/
		private function relocateSpace(string $entityFormerName):object {

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
		*	@return Result of evaluating {initialize}
		*/
		public function genericFactory (string $classDefinition, array $types, callable $initialize):object {

		    foreach ($types as $placeholder => $type)

		        $classDefinition = str_replace("<$placeholder>", $type, $classDefinition);

		    eval($classDefinition);

		    return $initialize($types);
		}

		/**
		*	Takes a class with circular dependencies and returns a proxy
		*/
		private function breakCircular(string $roundCaller):object {

			$reflectedClass = new ReflectionClass(CircularBreaker::class);

			$breaker = file_get_contents($reflectedClass->getFileName());

			return $this->genericFactory($breaker, ["target" => $roundCaller ], function ($types) {

			    return new CircularBreaker($types["target"], $this);
			});
		}
	}
?>