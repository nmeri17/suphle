<?php

	namespace Validators;

	use Tilwa\Sources\BaseValidator;

	use Models\User;

	class Authentication extends BaseValidator {

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

			if (!$noSuchUser) $allErrors[] = 'User already has this email';

			return $allErrors;
		}
	}

?>