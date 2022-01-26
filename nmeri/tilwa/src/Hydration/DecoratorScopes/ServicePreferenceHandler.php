<?php
	namespace Tilwa\Hydration\DecoratorScopes;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifiesArguments;

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class ServicePreferenceHandler implements ModifiesArguments {

		public function transformConstructor ($dummyInstance, array $injectedArguments):array {

			$suspects = [];

			foreach ($injectedArguments as $service)
				
				if ( !$this->containsParent($dummyInstance->getPermitted(), $service ))

					$suspects[] = $service;

			foreach ($suspects as $service)

				if ($this->containsParent($dummyInstance->getRejected(), $service ))

					throw new UnacceptableDependency (get_class($dummyInstance), get_class($service));
			
			return $injectedArguments;
		};

		public function transformMethods ($concreteInstance, array $arguments):array {

			//
		}

		private function containsParent (array $parentList, $dependency):bool {

			foreach ($parentList as $type)

				if ($dependency instanceof $type) return true;

			return false;
		}
	}
?>