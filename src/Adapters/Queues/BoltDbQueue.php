<?php
	namespace Suphle\Adapters\Queues;

	use Suphle\Contracts\IO\EnvAccessor;

	use Suphle\Hydration\Container;

	use Spiral\RoadRunner\Jobs\{Consumer, Jobs, Task\ReceivedTaskInterface};

	use Spiral\Goridge\RPC\RPC;

	use Spiral\RoadRunner\Environment;

	use Throwable;

	class BoltDbQueue extends BaseQueueAdapter {

		final const HEADER_ATTEMPTS = "attempts",

		HEADER_RETRY_DELAY = "retry-delay";

		private $envAccessor, $container, $maxRetries;

		public function __construct (EnvAccessor $envAccessor, Container $container) {

			$this->envAccessor = $envAccessor;

			$this->container = $container;

			$this->maxRetries = $envAccessor->getField("MAX_QUEUE_RETRIES", 5);
		}

		public function pushAction (string $taskClass, array $payload):void {

			$queue = $this->client->connect($this->activeQueueName);

			$task = $queue->create($taskClass, $payload)

			->withHeader(self::HEADER_ATTEMPTS, $this->maxRetries);

			$queue->dispatch($task);
		}

		// connection opened here is for the server
		public function processTasks ():void {

			$consumer = new Consumer;

			while ($task = $consumer->waitTask()) {

				try {

					$this->handleIncomingTask($task);

					$task->complete();
				}
				catch (Throwable $exception) {

					$this->onTaskFailure($task, $exception);
				}
			}
		}

		protected function handleIncomingTask (ReceivedTaskInterface $task):void {

			$className = $task->getName();

			$instance = $this->container->whenType($className)

			->needsArguments($task->getPayload())

			->getClass($className)->handle();
		}

		protected function onTaskFailure (ReceivedTaskInterface $task, Throwable $exception):void {

			$currentAttempts = intval($task->getHeaderLine(self::HEADER_ATTEMPTS));

			$delayInterval = intval($task->getHeaderLine(self::HEADER_RETRY_DELAY));

			$task->withHeader(self::HEADER_ATTEMPTS, $currentAttempts - 1)

			->withHeader(self::HEADER_RETRY_DELAY, $delayInterval * 2) // to be read on the next possibly failing iteration

			->withDelay($delayInterval)

			->fail($exception, $currentAttempts > intval($this->maxRetries));
		}

		// connection opened here is for the client
		public function configureNative ():void {

			$rpcAddress = Environment::fromGlobals()->getRPCAddress();

			$this->client = new Jobs(RPC::create($rpcAddress));
		}
	}
?>