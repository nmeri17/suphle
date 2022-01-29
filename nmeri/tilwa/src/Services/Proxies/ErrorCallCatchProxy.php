<?php
	namespace Tilwa\Services\Proxies;

	use Throwable;

	class ErrorCallCatchProxy extends BaseCallProxy {

		public function artificial__call (string $method, array $arguments) {

			$result = null;

			try {

				$result = $this->yield($method, $arguments);
			}
			catch (Throwable $exception) {

				$result = $this->attemptDiffuse($exception, $method);
			}
			
			return $result;
		}
	}
?>