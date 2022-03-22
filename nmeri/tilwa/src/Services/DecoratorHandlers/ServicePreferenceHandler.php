<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifiesArguments;

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class ServicePreferenceHandler implements ModifiesArguments {

		public function transformConstructor ($dummyInstance, array $injectedArguments):array {

			$permitted = $dummyInstance->getPermitted();

			$hasFriends = !empty($permitted);

			foreach ($injectedArguments as $service) {
				
				$stranger = $hasFriends && !$this->containsParent($permitted, $service );

				$enemy = !$hasFriends && $this->containsParent($dummyInstance->getRejected(), $service );

				if ($stranger || $enemy)

					throw new UnacceptableDependency (get_class($dummyInstance), get_class($service));
			}
			
			return $injectedArguments;
		}

		public function transformMethods ($concreteInstance, array $arguments):array {

			return $arguments;
		}

		private function containsParent (array $parentList, $dependency):bool {

			foreach ($parentList as $type)

				if ($dependency instanceof $type) return true;

			return false;
		}
	}
?>