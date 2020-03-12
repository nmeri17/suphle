<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	use Models\User;


	class Authentication extends BaseSource {

		public $validator = "Validators\Authentication";

		public function showForm( array $reqData, array $reqPlaceholders, array $validationErrors) {

			if (!empty($validationErrors)) {

				$badInput[0] = $validationErrors;
				var_dump($badInput);
				return $this->formatForEngine($badInput);
			}

			return [[]];
		}

		// confirm user doesn't exist, create one and send verification email
		public function signup ( array $reqData, array $reqPlaceholders, array $validationErrors) {

			$manager = $this->app->connection;

			//$qb = $manager->createQueryBuilder();

			$userRepo = $manager->getRepository(User::class);

			$reqData['password'] = password_hash($reqData['password'], PASSWORD_DEFAULT);

			$nUser = $userRepo->create(new User, $reqData);
var_dump($nUser); // check if password was updated
die();
			
			return $this->formatForEngine([['message' => 'user successfully created. kindly verify your account in your email']] ); // TODO: change the destination from reload to profile or homepage and alert
		}

	 	protected function semanticTransforms ():array {

	 		return [

	 			'validationErrors' => function ($val) {

	 				return ['message' => $val];
	 			}
	 		];
	 	}
	}

?>