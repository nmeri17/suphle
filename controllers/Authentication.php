<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	use Models\User;


	class Authentication extends BaseSource {

		public $validator = "Validators\Authentication";

		public function showRegisterForm( array $reqData, array $reqPlaceholders, array $validationErrors) {

			if (!empty($validationErrors)) {

				$reqData['validationErrors'] = $validationErrors;
				
				return $reqData;
			}

			return [[]];
		}

		// confirm user doesn't exist, create one and send verification email
		public function signup ( array $reqData, array $reqPlaceholders, array $validationErrors) {

			$manager = $this->app->connection;

			$nUser = $manager->getRepository(User::class)

			->create(new User, $reqData);

			$nUser->password = password_hash($reqData['password'], PASSWORD_DEFAULT);

			$nUser->verificationCode = bin2hex(openssl_random_pseudo_bytes(14));

			$this->sendVerificationMail($nUser, $reqData); 

			$manager->persist($nUser);

			$manager->flush();

			$_SESSION['tilwa_user_id'] = $nUser->id;
			
			return ['verify' => 'success']; // user successfully created. verify your account in your email
		}

		private function sendVerificationMail (User $user, array $reqData) {

			$siteName = $this->app->siteName;

			$email = $reqData['email'];

			$url = 'http://' . $siteName . '/verify-email?' . http_build_query([

				'email' => $user->email,

				'code' => $user->verificationCode
			]);

			$meta = [
			    'From' => 'webmaster@example.com',
			    'Content-type'=> 'text/html; charset=iso-8859-1'
			];

			mail($email, 'Email Account Verification for ' . $siteName, "<a href=$url>Click here to verify your account</a>", $meta); // can pull the template engine from the container and wire your template+data into it
		}

	 	public function semanticTransforms ():array {

	 		return [

	 			'validationErrors' => function ($val) {

	 				return ['message' => $val];
	 			}
	 		];
	 	}
		
		public function signin ( array $reqData) {

			$_SESSION['tilwa_user_id'] = $this->app->connection

			->getRepository(User::class)->findBy([

				'email' => $reqData['email']
			])[0]->id;

			return [];
		}
		
		public function showLoginForm ( array $reqData, array $reqPlaceholders, array $validationErrors) {

			return $this->showRegisterForm(...func_get_args());
		}
		
		public function signout () {

			unset($_SESSION['tilwa_user_id']);

			return [];
		}
	}

?>