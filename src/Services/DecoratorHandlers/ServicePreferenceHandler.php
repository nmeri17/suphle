<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Exception\Explosives\Generic\UnacceptableDependency;

	class ServicePreferenceHandler extends BaseArgumentModifier {

		public function transformConstructor (object $dummyInstance, array $injectedArguments):array {

			$permitted = $dummyInstance->getPermitted();

			$hasFriends = !empty($permitted);

			foreach ($injectedArguments as $service) {
				
				$stranger = $hasFriends && !$this->containsParent(

					$permitted, $service
				);

				$enemy = !$hasFriends && $this->containsParent(

					$dummyInstance->getRejected(), $service
				);

				if ($stranger || $enemy)

					throw new UnacceptableDependency (

						$dummyInstance::class, $service::class
					);
			}
			
			return $injectedArguments;
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