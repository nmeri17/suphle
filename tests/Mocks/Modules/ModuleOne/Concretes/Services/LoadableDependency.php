<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Contracts\Services\Decorators\OnlyLoadedBy;

	class LoadableDependency implements OnlyLoadedBy {

		public function allowedConsumers ():array {

			return [LoadablesChosenOne::class];
		}
	}
?>