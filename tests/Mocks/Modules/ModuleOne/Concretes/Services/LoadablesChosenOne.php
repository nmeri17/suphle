<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	class LoadablesChosenOne {

		public function __construct(private readonly LoadableDependency $dependency)
  {
  }

		public function getLoadable ():LoadableDependency {

			return $this->dependency;
		}
	}
?>