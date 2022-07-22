<?php
	namespace Suphle\Adapters\Orms\Eloquent\Condiments;

	use Suphle\Contracts\Auth\{ModelAuthorities, AuthStorage};

	use Suphle\Hydration\Structures\ObjectDetails;

	use Illuminate\Database\Eloquent\Relations\{HasOneOrMany, HasManyThrough};

	use ReflectionMethod;

	abstract class BaseEloquentAuthorizer implements ModelAuthorities {

		protected $authStorage, $objectMeta,

		$childrenTypes = [

			HasOneOrMany::class, HasManyThrough::class
		];

		public function __construct (AuthStorage $authStorage, ObjectDetails $objectMeta) {

			$this->authStorage = $authStorage;

			$this->objectMeta = $objectMeta;
		}

		/**
		 * @return string[] [method, names]
		*/
		protected function getChildrenMethods (string $modelName):array {

			$relationDetails = [];

			foreach ($this->objectMeta->getPublicMethods($modelName) as $methodName) {

				$isInherited = (new ReflectionMethod($modelName, $methodName))->class != $modelName;

				$returnType = $this->objectMeta->methodReturnType(

					$modelName, $methodName
				);

				$isNotRelation = !array_intersect(
				
					class_parents($returnType), $this->childrenTypes
				);

				if ($isInherited || $isNotRelation)

					continue;

				$relationDetails[] = $methodName;
			}

			return $relationDetails;
		}
	}
?>