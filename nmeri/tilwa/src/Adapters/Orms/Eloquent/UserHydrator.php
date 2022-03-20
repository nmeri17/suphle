<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Tilwa\Contracts\Auth\{UserContract, UserHydrator as HydratorContract};

	use Tilwa\Request\PayloadStorage;

	class UserHydrator implements HydratorContract {

		private $blankModel, $payloadStorage;

		public function __construct (User $user, PayloadStorage $payloadStorage) {

			$this->blankModel = $user;

			$this->payloadStorage = $payloadStorage;
		}

		public function findById (string $id):?UserContract {

			return $this->user->find($id);
		}

		/**
		 *  {@inheritdoc}
		*/
		public function findAtLogin ():?UserContract {

			return $this->user

			->where($this->payloadStorage->getKey("email"))

			->first();
		}
	}
?>