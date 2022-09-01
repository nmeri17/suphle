<?php
	namespace Suphle\Adapters\Orms\Eloquent;

	use Suphle\Contracts\Auth\{UserContract, UserHydrator as HydratorContract};

	use Suphle\Request\PayloadStorage;

	class UserHydrator implements HydratorContract {

		private $model;

		public function setUserModel (UserContract $model):void {

			$this->model = $model;
		}

		public function findById (string $id):?UserContract {

			return $this->model->find($id);
		}

		/**
		 *  {@inheritdoc}
		*/
		public function findAtLogin (array $criteria):?UserContract {

			return $this->model->where($criteria)->first();
		}
	}
?>