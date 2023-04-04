<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Explosives\DevError\HydrationException;

	use ReflectionClass, ReflectionException, ReflectionMethod, ReflectionType;

	class ObjectDetails {

		public function __construct (protected readonly Container $container) {

			//
		}

		public function getReflectedClass (string $className):ReflectionClass {

			try {

				return new ReflectionClass($className);
			}
			catch (ReflectionException $exception) {

				$message = "Unable to hydrate ". $this->container->lastHydratedFor() .  // in order to decouple problematic concretes from their consumers and give the error more context
				": ". $exception->getMessage();

				$hint = "Hint: Cross-check its dependencies";

				throw new HydrationException("$message. $hint");
			}
		}

		public function isInterface (string $entityName):bool {

			return $this->getReflectedClass($entityName)->isInterface();
		}

		public function classNamespace (string $entityName):string {
			
			return $this->getReflectedClass($entityName)->getNamespaceName();
		}

		public function implementsInterface (string $target, string $interface):bool {

			return in_array( $interface, class_implements($target) );
		}

		private function getReturnType (string $className, string $method):?ReflectionType {

			return (new ReflectionMethod($className, $method))

			->getReturnType();
		}

		public function methodReturnType (string $className, string $method):?string {

			$type = $this->getReturnType($className, $method);

			if ($type) {

				$typeName = $type->getName();

				if ($type->isBuiltin()) return $typeName;

				return "\\$typeName"; // adding forward slash so it can be used in other contexts without escaping
			}

			return null;
		}

		public function returnsBuiltIn (string $className, string $method):bool {

			$type = $this->getReturnType($className, $method);

			return $type ? $type->isBuiltin() : false;
		}

		public function stringInClassTree (string $childClass, string $parent):bool {

			return $this->implementsInterface($childClass, $parent) ||

			is_a($childClass, $parent, true); // argument 3 = accept string
		}

		public function getScalarValue (string $typeName) {

			$initial = null;

			if ($typeName == "mixed") 

				throw new HydrationException("Use more specific types, not $typeName");

			settype($initial, $typeName);

			return $initial;
		}

		/**
		 * @return interfaces on {interfaceList} that {entityName} actually implements
		*/
		public function parentInterfaceMatches (string $entityName, array $interfaceList ):array {

			return array_intersect(

				$interfaceList, class_implements($entityName)
			);
		}

		public function getValueType ($value):string {

			return is_object($value)? $value::class : gettype($value);
		}

		public function getPublicMethods (string $className):array {

			$methods = array_filter(
				get_class_methods($className),

				function ($methodName) use ($className) {

					return (new ReflectionMethod($className, $methodName))

					->isPublic();
				}
			);

			unset($methods[
				
				array_search(Container::CLASS_CONSTRUCTOR, $methods)
			]);

			return $methods;
		}

		/**
		 * Avoids calling its constructor
		 * 
		 * @return class instance
		*/
		public function noConstructor (string $className):object {

			return $this->getReflectedClass($className)

			->newInstanceWithoutConstructor();
		}

		/**
		 * The native method returns only attribute applied on the class itself
		*/
		public function getClassAttributes (string $className, string $filterToAttribute = null):array {

			$attributesList = [];

			$inheritanceChain = class_parents($className);

			$inheritanceChain[] = $className;

			foreach ($inheritanceChain as $entry)

				$attributesList = array_merge(

					$attributesList, $this->getReflectedClass($entry)

					->getAttributes($filterToAttribute)
				);

			return $attributesList;
		}

		/**
		 * @see https://stackoverflow.com/a/27440555/4678871

		 * I'm using this boilerplate method instead of a library since that doesn't iterate directories recursively
		 * 
		 * @return FQCN or "" if no class is found in file
		*/
		public function classNameFromFile (string $fileName):?string {

			if (!preg_match("/\.php$/", $fileName)) return null;

			$tokens = token_get_all(file_get_contents($fileName));
			
			$namespace = "";
			
			for ($index = 0; isset($tokens[$index]); $index++) {
				
				if (!isset($tokens[$index][0])) continue;

				if (
					T_NAMESPACE === $tokens[$index][0] &&

					T_WHITESPACE === $tokens[$index + 1][0] &&

					T_NAME_QUALIFIED === $tokens[$index + 2][0]
				) {
					$namespace = $tokens[$index + 2][1];
					// Skip "namespace" keyword, whitespaces, and actual namespace
					$index += 2; // forward to next token
				}
				
				if (
					T_CLASS === $tokens[$index][0] &&
					
					T_WHITESPACE === $tokens[$index + 1][0] &&
					
					T_STRING === $tokens[$index + 2][0]
				) {
					return $namespace. "\\".$tokens[$index + 2][1];
					// Skip "class" keyword, whitespaces, and actual classname
				}
			}

			return null;
		}
	}
?>