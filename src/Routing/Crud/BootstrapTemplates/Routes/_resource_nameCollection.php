<?php
	namespace _modules_shell\_module_name\Routes;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use _modules_shell\_module_name\Coordinators\_resource_nameCoordinator;

	#[HandlingCoordinator(_resource_nameCoordinator::class)]
	class _resource_nameCollection extends BaseCollection {

		public function _resource_route () {

			$this->_crud("_resource_name")->registerCruds();
		}
	}
?>