<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\Generic\HydrationException;

	use ReflectionClass, ReflectionException, ReflectionMethod, ReflectionType;

	class ObjectDetails {

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
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

			return $type ? "\\" . $type->getName() : // adding forward slash so it can be used in other contexts without escaping

			$type;
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

			settype($initial, $typeName);

			return $initial;
		}

		public function parentInterfaceMatches (string $entityName, array $interfaceList ):array {

			return array_intersect(

				$interfaceList, class_implements($entityName)
			);
		}

		public function getValueType ($value):string {

			return is_object($value)? get_class($value): gettype($value);
		}
	}
?>