<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	class FlowService {

		public function customHandlePrevious (iterable $models):iterable {

			return array_map(function ($model) {

				return $model["id"] * 2;
			}, $models);
		}
	}
?>