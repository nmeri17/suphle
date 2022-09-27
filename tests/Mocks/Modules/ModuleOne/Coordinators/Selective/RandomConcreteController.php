<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Selective;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

	class RandomConcreteController extends ServiceCoordinator {

		private $bCounter;

		public function __construct (BCounter $bCounter) {

			$this->bCounter = $bCounter;
		}
	}
?>