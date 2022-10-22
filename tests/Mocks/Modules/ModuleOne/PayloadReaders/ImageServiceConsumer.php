<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders;

	use Suphle\Services\Structures\ImagefulPayload;

	class ImageServiceConsumer extends ImagefulPayload {

		protected function convertToDomainObject () {

			return $this->imageOptimizer->setImages( // single endpoints with the need to handle multiple upload types should be split into as many ImagefulPayloads as necessary, for possible reuse

				$this->allFiles,

				$this->payloadStorage->getKey("belonging_resource")
			);
		}
	}
?>