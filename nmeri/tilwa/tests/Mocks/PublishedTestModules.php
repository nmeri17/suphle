<?php
	namespace Tilwa\Tests\Mocks;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Tests\Integration\Generic\TestsModuleList;

	class PublishedTestModules extends ModuleHandlerIdentifier {

		use TestsModuleList;

		public function __construct () {

			$this->setAllDescriptors();

			parent::__construct();
		}
		
		public function getModules ():array {

			return $this->getAllDescriptors();
		}
	}
?>