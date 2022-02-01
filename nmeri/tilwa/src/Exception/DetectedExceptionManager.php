<?php
	namespace Tilwa\Exception;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Exception\Jobs\DeferExceptionAlert;

	use Tilwa\Request\PayloadStorage;

	use Throwable;

	class DetectedExceptionManager {

		private $queueManager, $payloadStorage;

		public function __construct (AdapterManager $queueManager, PayloadStorage $payloadStorage) {

			$this->queueManager = $queueManager;

			$this->payloadStorage = $payloadStorage;
		}

		public function detonateOrDiffuse (Throwable $exception, ServiceErrorCatcher $thrower):void {

			$rebounds = $thrower->rethrowAs();

			$exceptionName = get_class($exception);

			if (array_key_exists($exceptionName, $rebounds))

				throw new $rebounds[$exceptionName];

			$this->triggerAlertAdapter($exception, $this->payloadStorage);
		}

		public function triggerAlertAdapter (Throwable $exception, $payload):void {

			$this->queueManager->augmentArguments(DeferExceptionAlert::class, [

				"explosive" => $exception,

				"activePayload" => $payload
			]);
		}
	}
?>