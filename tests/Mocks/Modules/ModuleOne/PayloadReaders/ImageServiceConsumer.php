<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders;

	use Suphle\Services\Structures\ModellessPayload;

	class ImageServiceConsumer extends ModellessPayload {

		protected function convertToDomainObject () {

			return $this->payloadStorage->getKey("belonging_resource");
		}
	}
?>