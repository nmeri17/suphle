<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ImageUploadController;

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

		public function APPLY__CROPh () {

			$this->_post(new Json("applyThumbnail"));
		}
	}
?>