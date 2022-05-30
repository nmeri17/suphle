<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifiesArguments;

	use Tilwa\Hydration\Structures\ObjectDetails;

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class ServicePreferenceHandler implements ModifiesArguments {

		private $objectMeta;

		public function __construct ( ObjectDetails $objectMeta) {

			$this->objectMeta = $objectMeta;
		}

		public function transformConstructor (object $dummyInstance, array $injectedArguments):array {

			$permitted = $dummyInstance->getPermitted();

			$hasFriends = !empty($permitted);

			foreach ($injectedArguments as $service) {
				
				$stranger = $hasFriends && !$this->containsParent($permitted, $service );

				$enemy = !$hasFriends && $this->containsParent($dummyInstance->getRejected(), $service );

				if ($stranger || $enemy)

					throw new UnacceptableDependency (

						get_class($dummyInstance), get_class($service)
					);
			}
			
			return $injectedArguments;
		}

		public function transformMethods (object $concreteInstance, array $arguments):array {

			return $arguments;
		}

		/**
		 * @param {dependency} mixed. Can be any type passed as argument
		*/
		private function containsParent (array $parentList, $dependency):bool {

			$dependencyType = $this->objectMeta->getValueType($dependency);

			foreach ($parentList as $typeToMatch) {

				if (is_object($dependency)) {

					if ($this->objectMeta->stringInClassTree(

						$dependencyType, $typeToMatch
					))
					return true;
				}

				else if ($dependencyType == $typeToMatch) return true;
			}

			return false;
		}
	}
?>