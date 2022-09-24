<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\Selective;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Hydration\Container;

	class ForbiddenDependencyController extends ServiceCoordinator {

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}
	}
?>