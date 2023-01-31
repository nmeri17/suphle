<?php
	namespace Suphle\Response\Format;

	use Suphle\Request\PayloadStorage;

	use Symfony\Component\HttpFoundation\File\{File, Exception\FileException};

	use Closure;

	class LocalFileDownload extends Redirect {

		public function __construct(

			protected string $handler, protected Closure $deriveFilePath,

			protected ?Closure $fallbackRedirect = null
		) {

			$this->statusCode = 200;
		}

		protected function serializableProperties ():array {

			return [ "deriveFilePath", "fallbackRedirect" ];
		}

		public function render ():string {
			
			$fileObject = $this->getFileObject();

			$this->setDownloadHeaders($fileObject);

			return $fileObject->getContent();
		}

		/**
		 * @throws FileException
		*/
		protected function getFileObject ():File {

			try {

				return new File(

					$this->callbackDetails->recursiveValueDerivation(

						$this->deriveFilePath, $this
					)
				);
			}
			catch (FileException $exception) {

				if (is_null($this->fallbackRedirect)) throw $exception;

				$this->statusCode = 404;

				return $this->renderRedirect($this->fallbackRedirect);
			}
		}

		protected function setDownloadHeaders (File $fileObject):void {

			$fileName = $fileObject->getFileName();

			$this->headers = array_merge($this->headers, [

				PayloadStorage::CONTENT_TYPE_KEY => $fileObject->getMimeType(),

				"Content-Disposition" => "attachment; filename='$fileName'",

				"Content-Length" => mb_strlen($fileObject->getContent()),

				"Connection" => "Keep-Alive"
			]);
		}
	}
?>