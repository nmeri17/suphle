<?php
	namespace Suphle\Exception;

	use Suphle\Contracts\Services\CallInterceptors\ServiceErrorCatcher;

	use Suphle\Queues\AdapterManager;

	use Suphle\Exception\Jobs\DeferExceptionAlert;

	use Throwable;

	class DetectedExceptionManager {

		final const ALERTER_METHOD = "queueAlertAdapter";

		public function __construct(protected readonly AdapterManager $queueManager) {

			//
		}

		public function detonateOrDiffuse (Throwable $exception, ServiceErrorCatcher $thrower, $payload):void {

			$rebounds = $thrower->rethrowAs();

			$exceptionName = $exception::class;

			if (array_key_exists($exceptionName, $rebounds))

				throw new $rebounds[$exceptionName];

			$this->queueAlertAdapter($exception, $thrower->getDebugDetails());
		}

		public function queueAlertAdapter (Throwable $exception, $payload):void {

			$this->queueManager->addTask(DeferExceptionAlert::class, [

				"explosive" => $exception,

				"activePayload" => $payload
			]);
		}
	}
?>