<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\Generic\HydrationException;

	use ReflectionClass, ReflectionException, ReflectionMethod;

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

		public function methodReturnType (string $className, string $method):?string {

			$type = (new ReflectionMethod($className, $method))

			->getReturnType();

			if (!is_null($type)) return $type->getName();

			return null;
		}
	}
?>