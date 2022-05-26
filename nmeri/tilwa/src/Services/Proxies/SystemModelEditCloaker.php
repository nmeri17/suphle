<?php
	namespace Tilwa\Services\Proxies;

	use Tilwa\File\FileSystemReader;

	use Tilwa\Hydration\Structures\ObjectDetails;

	class SystemModelEditCloaker extends BaseCloakBuilder {

		public function __construct ( SystemModelCallProxy $serviceCallProxy, FileSystemReader $systemReader, ObjectDetails $objectMeta) {

			$this->serviceCallProxy = $serviceCallProxy;

			$this->systemReader = $systemReader;

			$this->objectMeta = $objectMeta;
		}
	}
?>