<?php
	namespace Tilwa\Tests\Integration\Generic;

	use Tilwa\Contracts\Config\{Router, ModuleFiles};

	use Tilwa\Contracts\IO\EnvAccessor;

	use Tilwa\Config\AscendingHierarchy;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, EnvRequiredSub};

	trait CommonBinds {

		protected function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				Router::class => RouterMock::class,

				EnvAccessor::class => EnvRequiredSub::class
			]);
		}

		protected function concreteBinds ():array {

			$anchorPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "Mocks/Modules/ModuleOne/Config";

			return array_merge(parent::concreteBinds(), [

				ModuleFiles::class => new AscendingHierarchy($anchorPath)
			]);
		}
	}
?>