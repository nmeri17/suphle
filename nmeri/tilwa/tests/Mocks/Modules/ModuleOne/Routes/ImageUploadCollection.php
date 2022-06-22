<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Response\Format\Json;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\ImageUploadController;

	class ImageUploadCollection extends BaseCollection {

		public function _handlingClass ():string {

			return ImageUploadController::class;
		}

		public function APPLY__ALLh () {

			$this->_post(new Json("applyAllOptimizations"));
		}

		public function APPLY__NONEh () {

			$this->_post(new Json("applyNoOptimization"));
		}
	}
?>