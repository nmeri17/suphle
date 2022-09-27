<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Selective;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\BlankUpdateless;

	class BlankUpdatelessController extends ServiceCoordinator {

		private $dependency;

		public function __construct (BlankUpdateless $dependency) {

			$this->dependency = $dependency;
		}
	}
?>