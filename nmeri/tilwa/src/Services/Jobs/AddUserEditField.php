<?php
	namespace Tilwa\Services\Jobs;

	use Tilwa\Contracts\{Database\OrmDialect, Queues\Task, Services\Models\IntegrityModel};

	class AddUserEditField implements Task {

		private $ormDialect, $editIdentifier, $modelInstance;

		public function __construct (OrmDialect $ormDialect, IntegrityModel $modelInstance, int $editIdentifier) {

			$this->ormDialect = $ormDialect;

			$this->editIdentifier = $editIdentifier;

			$this->modelInstance = $modelInstance;
		}

		public function handle () {

			$this->ormDialect->runTransaction(function () {

				$this->modelInstance->addEditIntegrity( $this->editIdentifier);
				
			}, [$this->modelInstance]);
		}
	}
?>