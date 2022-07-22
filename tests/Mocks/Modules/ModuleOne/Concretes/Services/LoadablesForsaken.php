<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	class LoadablesForsaken {

		private $dependency;

		public function __construct (LoadableDependency $dependency) {

			$this->dependency = $dependency;
		}
	}
?>