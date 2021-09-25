<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne;

	use Tilwa\App\ModuleDescriptor;

	use Tilwa\Contracts\Config\{ Services as IServices, Laravel as ILaravel, Router as IRouter, Auth as IAuth, Transphporm as ITransphporm, ModuleFiles as IModuleFiles}; // continue here

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, TransphpormMock, ModuleFilesMock};

	class ModuleOneDescriptor extends ModuleDescriptor {

		public function getConfigs():array {
			
			return parent::getConfigs() + [

				IModuleFiles::class => ModuleFilesMock::class
			];
		}
	}
?>