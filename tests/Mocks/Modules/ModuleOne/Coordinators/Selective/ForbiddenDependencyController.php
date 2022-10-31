<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Selective;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Hydration\Container;

	class ForbiddenDependencyController extends ServiceCoordinator {

		public function __construct(private readonly Container $container)
  {
  }
	}
?>