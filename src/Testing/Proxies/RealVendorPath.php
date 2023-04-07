<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\File\FileSystemReader;

	use Suphle\Server\VendorBin;

	trait RealVendorPath {

		protected ?VendorBin $vendorBin = null;

		/**
		 * @return The path 2 steps before vendor/bin
		*/
		protected function getVendorParent ():string {

			return $this->getContainer()->getClass(FileSystemReader::class)

			->pathFromLevels($_SERVER["COMPOSER_RUNTIME_BIN_DIR"], "", 2);
		}

		protected function setVendorPath ():void {

			$this->vendorBin = $this->getContainer()->getClass(VendorBin::class);

			$this->vendorBin->setRootPath($this->getVendorParent());
		}
	}
?>