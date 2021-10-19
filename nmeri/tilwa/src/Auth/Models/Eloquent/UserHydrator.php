<?php
	namespace Tilwa\Auth\Models\Eloquent;

	use Tilwa\Contracts\Auth\UserHydrator;

	use Tilwa\Request\PayloadStorage;

	class UserHydrator implements UserHydrator {

		private $blankModel, $payloadStorage;

		public function __construct (User $user, PayloadStorage $payloadStorage) {

			$this->blankModel = $user;

			$this->payloadStorage = $payloadStorage;
		}

		public function findById(string $id):User {

			return $this->user->find($id);
		}

		/**
		 *  {@inheritdoc}
		*/
		public function findAtLogin():User {

			return $this->user

			->where($this->payloadStorage->only(["email"]))

			->first();
		}
	}
?>