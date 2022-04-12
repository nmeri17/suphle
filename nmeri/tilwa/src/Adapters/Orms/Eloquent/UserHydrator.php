<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Contracts\Auth\{UserContract, UserHydrator as HydratorContract};

	use Tilwa\Request\PayloadStorage;

	class UserHydrator implements HydratorContract {

		private $blankModel, $payloadStorage;

		public function __construct (UserContract $user, PayloadStorage $payloadStorage) {

			$this->blankModel = $user;

			$this->payloadStorage = $payloadStorage;
		}

		public function findById (string $id):?UserContract {

			return $this->blankModel->find($id);
		}

		/**
		 *  {@inheritdoc}
		*/
		public function findAtLogin ():?UserContract {

			$column = "email";

			return $this->blankModel

			->where([

				$column => $this->payloadStorage->getKey($column)
			])->first();
		}
	}
?>