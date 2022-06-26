<?php
	namespace Tilwa\Adapters\Image\Optimizers;

	use Tilwa\Contracts\IO\Image\InferiorImageClient;

	use Tilwa\File\FileSystemReader;

	use ImageOptimizer\OptimizerFactory;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	class ImageOptimizerClient implements InferiorImageClient {

		private $client, $fileSystemReader;

		public function __construct (FileSystemReader $fileSystemReader) {

			$this->fileSystemReader = $fileSystemReader;
		}

		public function setupClient ():void {

			$this->client = (new OptimizerFactory())->get();
		}

		public function downgrade (string $currentPath):void {

			$this->client->optimize($currentPath);
		}

		public function moveDowngraded (UploadedFile $image, ?string $newPath):string {
var_dump(25, $newPath);

			$this->fileSystemReader->ensureDirectoryExists($newPath);

			$image->move(dirname($newPath), $newPath );

			return $newPath;
		}
	}
?>