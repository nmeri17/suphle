<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	use Models\User;


	class Authentication extends BaseSource {

		public $validator = "Validators\Authentication";

		public function showForm( array $reqData, array $reqPlaceholders, array $validationErrors) {

			if (!empty($validationErrors)) {

				$reqData['validationErrors'] = $validationErrors;
				
				return $reqData;
			}

			return [[]];
		}

		// confirm user doesn't exist, create one and send verification email
		public function signup ( array $reqData, array $reqPlaceholders, array $validationErrors) {

			$manager = $this->app->connection;

			//$qb = $manager->createQueryBuilder();

			$userRepo = $manager->getRepository(User::class);

			$nUser = $userRepo->create(new User, $reqData);

			$nUser->password = password_hash($reqData['password'], PASSWORD_DEFAULT);

			$manager->persist($nUser);

			$jnf = $manager->flush($nUser);
var_dump($jnf);
die();
			
			return $this->formatForEngine([['message' => 'user successfully created. kindly verify your account in your email']] ); // TODO: change the destination from reload to profile or homepage and alert
		}

	 	public function semanticTransforms ():array {

	 		return [

	 			'validationErrors' => function ($val) {

	 				return ['message' => $val];
	 			}
	 		];
	 	}
	}

?>