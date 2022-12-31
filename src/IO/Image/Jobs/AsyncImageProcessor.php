<?php
	namespace Suphle\IO\Image\Jobs;

	use Suphle\Contracts\{Queues\Task, IO\Image\ImageOptimiseOperation, Exception\AlertAdapter};

	use Throwable;

	class AsyncImageProcessor implements Task {

		public function __construct(protected readonly AlertAdapter $alerter, protected readonly ImageOptimiseOperation $operation) {

			//
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