<?php
	namespace Tilwa\Exception;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Exception\Jobs\DeferExceptionAlert;

	use Throwable;

	class DetectedExceptionManager {

		private $queueManager;

		public function __construct (AdapterManager $queueManager) {

			$this->queueManager = $queueManager;
		}

		public function detonateOrDiffuse (Throwable $exception, ServiceErrorCatcher $thrower):void {

			$rebounds = $thrower->rethrowAs();

			$exceptionName = get_class($exception);

			if (array_key_exists($exceptionName, $rebounds))

				throw new $rebounds[$exceptionName];

			$this->queueManager->augmentArguments(DeferExceptionAlert::class, [

				"explosive" => $exception
			]);
		}
	}
?>