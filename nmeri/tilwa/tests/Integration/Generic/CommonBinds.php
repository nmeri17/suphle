<?php
	namespace Tilwa\Tests\Integration\Generic;

	use Tilwa\Contracts\Config\{Router, ModuleFiles};

	use Tilwa\Config\AscendingHierarchy;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock};

	trait CommonBinds {

		protected function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				Router::class => RouterMock::class
			]);
		}

		protected function fileConfigModuleName ():string {

			return "ModuleOne";
		}

		protected function concreteBinds ():array {

			$anchorPath = dirname(__DIR__, 2) . "/Mocks/Modules/". $this->fileConfigModuleName() . "/Config";

			return array_merge(parent::concreteBinds(), [

				ModuleFiles::class => new AscendingHierarchy($anchorPath)
			]);
		}
	}
?>