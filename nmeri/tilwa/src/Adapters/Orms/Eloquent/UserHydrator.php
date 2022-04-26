<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Contracts\Auth\{UserContract, UserHydrator as HydratorContract};

	use Tilwa\Request\PayloadStorage;

	class UserHydrator implements HydratorContract {

		private $payloadStorage, $model,

		$loginColumnIdentifier = "email";

		public function __construct ( PayloadStorage $payloadStorage) {

			$this->payloadStorage = $payloadStorage;
		}

		public function setUserModel (UserContract $model):void {

			$this->model = $model;
		}

		public function findById (string $id):?UserContract {

			return $this->model->find($id);
		}

		/**
		 *  {@inheritdoc}
		*/
		public function findAtLogin ():?UserContract {

			return $this->model->where([

				$this->loginColumnIdentifier => $this->payloadStorage->getKey($this->loginColumnIdentifier)
			])->first();
		}
	}
?>