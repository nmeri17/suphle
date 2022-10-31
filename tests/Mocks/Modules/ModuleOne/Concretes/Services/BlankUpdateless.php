<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Services\UpdatelessService;

	use Suphle\Contracts\Auth\AuthStorage;

	// these methods are redundant to the class btw
	class BlankUpdateless extends UpdatelessService {

		public function __construct(protected AuthStorage $authStorage)
  {
  }

		public function updateModels () {

			return true;
		}

		public function modelsToUpdate ():array {

			return [];
		}

		public function initializeUpdateModels ($baseModel):void {

			//
		}
	}
?>