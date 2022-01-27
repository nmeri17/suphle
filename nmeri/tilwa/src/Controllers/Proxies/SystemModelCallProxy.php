<?php
	namespace Tilwa\Controllers\Proxies;

	use Tilwa\Contracts\Database\Orm;

	class SystemModelCallProxy extends BaseCallProxy {

		private $orm;

		public function __construct ( Orm $orm) {

			$this->orm = $orm;
		}

		protected function artificial__call (string $method, array $arguments) {

			if ($method == "updateModels") // restrict this from running on unrelated methods

				$this->orm->runTransaction(function () use ($method, $arguments) {

					$this->yield($method, $arguments);
				});

			else return $this->yield($method, $arguments);
		}
	}
?>