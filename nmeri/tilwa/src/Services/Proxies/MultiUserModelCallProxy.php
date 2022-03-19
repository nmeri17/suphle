<?php
	namespace Tilwa\Services\Proxies;

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Exception\{Explosives\EditIntegrityException, DetectedExceptionManager};

	use Throwable, DateTime;

	/**
	 * The idea is that the last updater should invalidate whatever those with current copies of the page are both looking at or trying to update
	*/
	class MultiUserModelCallProxy extends BaseCallProxy {

		const INTEGRITY_KEY = "_collision_protect"; // submitted form/payload is expected to contain this key

		private $ormDialect, $queueManager, $payloadStorage;

		public function __construct (OrmDialect $ormDialect, AdapterManager $queueManager, PayloadStorage $payloadStorage, DetectedExceptionManager $exceptionDetector) {

			parent::__construct($exceptionDetector);

			$this->ormDialect = $ormDialect;

			$this->queueManager = $queueManager;

			$this->payloadStorage = $payloadStorage;
		}

		public function artificial__call (string $method, array $arguments) {

			if ($method == "getResource") // should getting editable resource fail, there's nothing to fallback on. Terminate request by bubbling up 

				return $this->activeService->getResource();
			
			if ($method == "updateResource")

				return $this->handleUpdateResource($arguments);

			return $this->yield($method, $arguments); // calling other methods is allowed, but not protected
		}

		/**
		 * @throws EditIntegrityException
		*/
		private function handleUpdateResource (array $arguments) {

			if (!$this->payloadStorage->hasKey(self::INTEGRITY_KEY))

				throw new EditIntegrityException;

			$currentVersion = $this->activeService->getResource();

			if (!$currentVersion->includesEditIntegrity($this->payloadStorage->getKey(self::INTEGRITY_KEY)) ) // this is the main part of the entire setup

				throw new EditIntegrityException;

			try {

				return $this->ormDialect->runTransaction(function () use ($currentVersion) {

					$result = $this->activeService->updateResource(); // user's incoming changes

					$currentVersion->nullifyEditIntegrity(new DateTime("y-m-d H:i:s"));

					return $result;

				}, [$currentVersion], true);
			}
			catch (Throwable $exception) {

				return $this->attemptDiffuse($exception, "updateResource");
			}
		}
	}
?>