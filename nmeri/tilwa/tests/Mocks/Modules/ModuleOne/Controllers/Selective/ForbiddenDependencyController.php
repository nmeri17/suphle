<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\Selective;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Hydration\Container;

	class ForbiddenDependencyController extends ServiceCoordinator {

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}
	}
?>