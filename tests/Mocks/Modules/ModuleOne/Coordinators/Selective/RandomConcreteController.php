<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Selective;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

	class RandomConcreteController extends ServiceCoordinator {

		public function __construct(private readonly BCounter $bCounter)
  {
  }
	}
?>