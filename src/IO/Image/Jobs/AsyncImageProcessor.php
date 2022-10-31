<?php
	namespace Suphle\IO\Image\Jobs;

	use Suphle\Contracts\{Queues\Task, IO\Image\ImageOptimiseOperation, Exception\AlertAdapter};

	use Throwable;

	class AsyncImageProcessor implements Task {

		private $operation, $alerter;

		public function __construct ( AlertAdapter $alerter, ImageOptimiseOperation $operation) {

			$this->operation = $operation;

			$this->alerter = $alerter;
		}

		public function handle ():void {

			$operation = null;
   try {

				$this->operation->getTransformed();
			}
			catch (Throwable $exception) {

				$this->alerter->broadcastException($exception, $operation);
			}
		}
	}
?>