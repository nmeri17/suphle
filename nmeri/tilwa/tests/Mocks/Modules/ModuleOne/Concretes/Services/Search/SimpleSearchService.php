<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\Search;

	use Tilwa\Services\Search\SimpleSearch;

	class SimpleSearchService extends SimpleSearch {

		protected function custom_filter ($model, $value) {

			return $model;
		}	
	}
?>