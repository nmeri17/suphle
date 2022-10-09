<?php
	namespace Suphle\Exception;

	use Suphle\Contracts\Services\Decorators\ServiceErrorCatcher;

	use Suphle\Queues\AdapterManager;

	use Suphle\Exception\Jobs\DeferExceptionAlert;

	use Throwable;

	class DetectedExceptionManager {

		const ALERTER_METHOD = "queueAlertAdapter";

		private $queueManager;

		public function __construct (AdapterManager $queueManager) {

			$this->queueManager = $queueManager;
		}

		public function detonateOrDiffuse (Throwable $exception, ServiceErrorCatcher $thrower, $payload):void {

			$rebounds = $thrower->rethrowAs();

			$exceptionName = get_class($exception);

			if (array_key_exists($exceptionName, $rebounds))

				throw new $rebounds[$exceptionName];

			$this->queueAlertAdapter($exception, $thrower->getDebugDetails());
		}

		public function queueAlertAdapter (Throwable $exception, $payload):void {

			$this->queueManager->augmentArguments(DeferExceptionAlert::class, [

				"explosive" => $exception,

				"activePayload" => $payload
			]);
		}
	}
?>