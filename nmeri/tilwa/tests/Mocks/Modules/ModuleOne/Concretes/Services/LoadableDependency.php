<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Contracts\Services\Decorators\OnlyLoadedBy;

	class LoadableDependency implements OnlyLoadedBy {

		public function allowedConsumers ():array {

			return [LoadablesChosenOne::class];
		}
	}
?>