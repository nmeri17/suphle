<?php
	namespace Tilwa\IO\Image\Jobs;

	use Tilwa\Contracts\{Queues\Task, IO\ImageSaver, Exception\AlertAdapter};

	use Throwable;

	class AsyncImageProcessor implements Task {

		private $imageSaver, $operations, $imageNames, $alerter;

		public function __construct (ImageSaver $imageSaver, AlertAdapter $alerter, , array $operations, array $imageNames) {

			$this->imageSaver = $imageSaver;

			$this->operations = $operations;

			$this->imageNames = $imageNames;

			$this->alerter = $alerter;
		}

		public function handle ():void {

			foreach ($this->operations as $operationName => $operation)

				try {

					$filesAndNames = array_combine(

						$this->imageNames[$operationName],

						$operation->getTransformed()
					);

					$this->imageSaver->transportImagesAsync($filesAndNames);
				}
				catch (Throwable $exception) {

					$this->alerter->broadcastException($exception, $operation);
				}
		}
	}
?>