<?php
	namespace Tilwa\Controllers\Jobs;

	use Tilwa\Contracts\{Database\Orm, Queues\Task, Services\Models\IntegrityModel};

	class UserEditFieldUpdate implements Task {

		private $orm, $editIdentifier, $modelInstance;

		public function __construct (Orm $orm, IntegrityModel $modelInstance, int $editIdentifier) {

			$this->orm = $orm;

			$this->editIdentifier = $editIdentifier;

			$this->modelInstance = $modelInstance;
		}

		public function handle () {

			$this->orm->runTransaction(function () {

				$this->modelInstance->setEditIntegrity( $this->editIdentifier);

				$this->orm->saveOne($this->modelInstance);
			});
		}
	}
?>