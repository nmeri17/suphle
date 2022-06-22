<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\PayloadReaders;

	use Tilwa\Services\Structures\ModellessPayload;

	class ImageServiceConsumer extends ModellessPayload {

		protected function convertToDTO () {

			return $this->payloadStorage->getKey("belonging_resource");
		}
	}
?>