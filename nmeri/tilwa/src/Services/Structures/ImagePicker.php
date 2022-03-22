<?php
	namespace Tilwa\Services\Structures;

	use Psr\Http\Message\ServerRequestFactoryInterface;

	abstract class ImagePicker {

		protected $files;

		public function __construct (ServerRequestFactoryInterface $serverFactory) {

			$this->files = $serverFactory::fromGlobals()->getUploadedFiles();
		}

		abstract public function toOptimize ():array;
	}
?>