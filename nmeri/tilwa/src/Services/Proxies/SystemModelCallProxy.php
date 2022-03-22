<?php
	namespace Tilwa\Services\Proxies;

	use Tilwa\Contracts\Database\Orm;

	use Tilwa\Exception\DetectedExceptionManager;

	use Throwable;

	class SystemModelCallProxy extends BaseCallProxy {

		private $orm;

		public function __construct ( Orm $orm, DetectedExceptionManager $exceptionDetector) {

			parent::__construct($exceptionDetector);

			$this->orm = $orm;
		}

		protected function artificial__call (string $method, array $arguments) {

			if ($method == "updateModels") // restrict this decorator from running on unrelated methods

				try {

					return $this->orm->runTransaction(function () use ($method) {

						return $this->yield($method);

					}, $this->activeService->modelsToUpdate());
				}
				catch (Throwable $exception) {

					return $this->attemptDiffuse($exception, $method);
				}

			return $this->yield($method, $arguments);
		}
	}
?>