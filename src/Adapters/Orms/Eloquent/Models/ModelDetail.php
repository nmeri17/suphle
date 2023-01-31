<?php
	namespace Suphle\Adapters\Orms\Eloquent\Models;

	use Suphle\Contracts\Database\EntityDetails;

	use ReflectionClass;

	class ModelDetail implements EntityDetails {

		public function idFromModel (object $model, string $prefix = ""):string {

			$primaryField = $model->getKeyName();

			return $this->idFromString(

				(new ReflectionClass($model))->getShortName(),

				$model->$primaryField, $prefix
			);
		}

		public function idFromString (string $modelName, string $modelId, string $prefix = ""):string {

			return strtolower(implode(

				"_", array_filter([$prefix, $modelName, $modelId]) // remove possible empty entries
			));
		}
	}
?>