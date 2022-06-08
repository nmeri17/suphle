<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\Search;

	use Tilwa\Services\Search\SimpleSearch;

	class SimpleSearchService extends SimpleSearch {

		public function custom_filter ($model, $value) {

			return $model;
		}	
	}
?>