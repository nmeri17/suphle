<?php
	namespace Tilwa\IO\Http;

	use Tilwa\Contracts\Services\Decorators\OnlyLoadedBy;

	use Tilwa\Controllers\ServiceCoordinator;

	class BaseHttpRequest implements OnlyLoadedBy {

		final public function allowedConsumers ():array {

			return [ServiceCoordinator::class];
		}
	}
?>