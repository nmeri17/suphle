<?php
	namespace Tilwa\IO\Image\Jobs;

	use Tilwa\Contracts\{Queues\Task, IO\ImageOptimiseOperation, Exception\AlertAdapter};

	use Throwable;

	class AsyncImageProcessor implements Task {

		private $operation, $alerter;

		public function __construct ( AlertAdapter $alerter, ImageOptimiseOperation $operation) {

			$this->operation = $operation;

			$this->alerter = $alerter;
		}

		public function handle ():void {

			try {

				$this->operation->getTransformed();
			}
			catch (Throwable $exception) {

				$this->alerter->broadcastException($exception, $operation);
			}
		}
	}
?>