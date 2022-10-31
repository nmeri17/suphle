<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	class LoadablesForsaken {

		public function __construct(private readonly LoadableDependency $dependency)
  {
  }
	}
?>