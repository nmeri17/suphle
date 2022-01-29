<?php
	namespace Tilwa\Services\Proxies;

	use Tilwa\Contracts\Database\Orm;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Exception\{Explosives\EditIntegrityException, DetectedExceptionManager};

	use Throwable;

	class MultiUserModelCallProxy extends BaseCallProxy {

		const INTEGRITY_KEY = "_collision_protect"; // submitted form/payload is expected to contain this key

		private $orm, $queueManager, $payloadStorage;

		public function __construct (Orm $orm, AdapterManager $queueManager, PayloadStorage $payloadStorage, DetectedExceptionManager $exceptionDetector) {

			parent::__construct($exceptionDetector);

			$this->orm = $orm;

			$this->queueManager = $queueManager;

			$this->payloadStorage = $payloadStorage;
		}

		public function artificial__call (string $method, array $arguments) {

			if ($method == "getResource") { // should getting editable resource fail, there's nothing to fallback on. Terminate request by bubbling up 

				$result = $this->getResource();

				$this->handleGetResource($result);

				return $result;
			}
			else if ($method == "updateResource")

				return $this->handleUpdateResource($arguments);

			return $this->yield($method, $arguments); // calling other methods is allowed, but not protected
		}

		private function handleGetResource (IntegrityModel $modelInstance):void {

			$editIdentifier = random_int(13, 24759);

			$this->queueManager->augmentArguments(

				UserEditFieldUpdate::class,

				compact("editIdentifier", "modelInstance")
			);

			$modelInstance->setEditIntegrity($editIdentifier);
		}

		/**
		 * @throws EditIntegrityException
		*/
		private function handleUpdateResource (array $arguments):void {

			if (!$this->payloadStorage->hasKey(self::INTEGRITY_KEY))

				throw new EditIntegrityException;

			$currentVersion = $this->activeService->getResource();

			if ($currentVersion->getEditIntegrity() != $this->payloadStorage->getKey(self::INTEGRITY_KEY))

				throw new EditIntegrityException;

			try {

				$this->orm->runTransaction(function () use ($currentVersion) {

					$this->activeService->updateResource(); // user's incoming changes

					$currentVersion->setEditIntegrity( null);

					$this->orm->saveOne($currentVersion);
				}, [$currentVersion], true);
			}
			catch (Throwable $exception) {

				$this->attemptDiffuse($exception, "updateResource");
			}
		}
	}
?>