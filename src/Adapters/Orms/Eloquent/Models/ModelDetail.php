<?php
	namespace Suphle\Adapters\Orms\Eloquent\Models;

	use Suphle\Contracts\Database\EntityDetails;

	use ReflectionClass;

	class ModelDetail implements EntityDetails {

		public function normalizeIdentifier (object $model, string $prefix = ""):string {

			$segments = [$prefix];

			$segments[] = (new ReflectionClass($model))->getShortName();

			$primaryField = $model->getKeyName();

			$segments[] = $model->$primaryField;

			return strtolower(implode("_", $segments));
		}
	}
?>