<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\Selective;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\BlankUpdateless;

	class BlankUpdatelessController extends ServiceCoordinator {

		private $dependency;

		public function __construct (BlankUpdateless $dependency) {

			$this->dependency = $dependency;
		}
	}
?>