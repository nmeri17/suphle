<?php
	namespace Suphle\Adapters\Orms\Eloquent;

	use Suphle\Contracts\Auth\{UserContract, UserHydrator as HydratorContract};

	use Suphle\Request\PayloadStorage;

	class UserHydrator implements HydratorContract {

		private $model;

		public function setUserModel (UserContract $model):void {

			$this->model = $model;
		}

		public function getUserById (string $id):?UserContract {

			$userInstance = $this->model->findByPrimaryKey($id);

			$userInstance->preventsLazyLoading = true; // setting this manually since neither the ORM-level shouldBeStrict nor setting it here works. If that property is false, tests comparing authenticated instance will fail

			return $userInstance;
		}

		/**
		 *  {@inheritdoc}
		*/
		public function findAtLogin (array $criteria):?UserContract {

			return $this->model->where($criteria)->first();
		}
	}
?>