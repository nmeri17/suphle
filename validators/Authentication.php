<?php

	// refactor this whole class to reflect validators now living on the request
	namespace Validators;

	use Models\User;

	class Authentication {

		public function signup ( array $reqData ) {

			$manager = $this->app->connection;

			$userRepo = $manager->getRepository(User::class);

			$noSuchUser = $userRepo->count( ['email'=> $reqData['email']]) === 0; // only applicable to doctrine

			$rules = [
				'first_name' => 'required|alpha|min:3',
				'last_name' => 'required|alpha|min:3',
				'email' => 'required|email',
				'password' => 'required|same:confirm_password|min:8',
			];

			$validator = $this->validator->make($reqData, $rules);

			if ($validator->validate() && $noSuchUser) return [];

			$allErrors = $validator->errors()->all();

			if (!$noSuchUser) $allErrors[] = 'Email already in use';

			return $allErrors;
		}

		public function signin ( array $reqData ) {

			$manager = $this->app->connection;

			$users = $manager->getRepository(User::class)->findBy([

				'email' => $reqData['email']
			]);

			if (empty($users) || !password_verify(

				$reqData['password'], $users[0]->password)
			) return ['Username or password incorrect'];

			return [];
		}
	}

?>