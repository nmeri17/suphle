<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	class LoadablesChosenOne {

		private $dependency;

		public function __construct (LoadableDependency $dependency) {

			$this->dependency = $dependency;
		}

		public function getLoadable ():LoadableDependency {

			return $this->dependency;
		}
	}
?>