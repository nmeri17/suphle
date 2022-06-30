<?php
	namespace Tilwa\Adapters\Image\Optimizers;

	use Tilwa\Contracts\IO\Image\InferiorImageClient;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	class NativeReducerClient implements InferiorImageClient {

		const JPEG = "jpeg", JPG = "jpg", PNG = "png", GIF = "gif";

		protected $readerFunctions = [

			self::JPEG => "imagecreatefromjpeg",

			self::JPG => "imagecreatefromjpeg",

			self::PNG => "imagecreatefrompng",

			self::GIF => "imagecreatefromgif"
		],

		$writerFunctions = [

			self::JPEG => "imagejpeg",

			self::JPG => "imagejpeg",

			self::PNG => "imagepng",

			self::GIF => "imagegif"
		];

		/**
		 * {@inheritdoc}
		 * 
		 * @param {newPath} Assumes a copy of image already exists at given location
		*/
		public function downgradeImage (UploadedFile $image, ?string $newPath, int $maxSize):string {

			if ($image->getSize() >= $maxSize)

				$this->compressByFormat(

					$image->guessExtension(), $newPath
				);

			return $newPath;
		}

		/**
		 * Compresses the image in place i.e no additional copying is being done
		*/
		public function compressByFormat (string $imageExtension, string $currentLocation) {

			if (!array_key_exists($imageExtension, $this->readerFunctions))
				
				$imageExtension = self::PNG;

			$readerFunction = $this->readerFunctions[$imageExtension];

			$writerFunction = $this->writerFunctions[$imageExtension];

			$imageResource = $readerFunction($currentLocation);

			$writerFunction($imageResource, $currentLocation); // argument 3 = image quality defaults to 75

			imagedestroy($imageResource);
		}
	}
?>