<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Selective;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\BlankUpdateless;

	class BlankUpdatelessController extends ServiceCoordinator {

		public function __construct(private readonly BlankUpdateless $dependency) {

			//
		}
	}
?>