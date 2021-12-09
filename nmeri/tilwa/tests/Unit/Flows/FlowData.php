<?php
	namespace Tilwa\Tests\Unit\Flows;

	trait FlowData {

		protected $payloadKey = "data", $indexes;

		protected function getIndexes ():array {

			$indexes = [];

			for ($i=1; $i < 11; $i++) $indexes[] = $i;

			return $indexes;
		}

		protected function indexesToModels ():array {

			return array_map(function ($id) {

				return compact("id");
			}, $this->indexes);
		}
	}
?>