<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\Selective;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

	class RandomConcreteController extends ServiceCoordinator {

		private $bCounter;

		public function __construct (BCounter $bCounter) {

			$this->bCounter = $bCounter;
		}
	}
?>