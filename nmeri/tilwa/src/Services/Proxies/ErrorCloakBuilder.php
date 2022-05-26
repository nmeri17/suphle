<?php
	namespace Tilwa\Services\Proxies;

	use Tilwa\File\FileSystemReader;

	use Tilwa\Hydration\Structures\ObjectDetails;

	class ErrorCloakBuilder extends BaseCloakBuilder {

		public function __construct ( ErrorCallCatchProxy $serviceCallProxy, FileSystemReader $systemReader, ObjectDetails $objectMeta) {

			$this->serviceCallProxy = $serviceCallProxy;

			$this->systemReader = $systemReader;

			$this->objectMeta = $objectMeta;
		}
	}
?>