<?php

	namespace Sources;

	use Tilwa\Sources\BaseSource;

	use Models\User;

	class Authentication extends BaseSource {

		public function showForm() {

			return $this->formatForEngine([[]] ); // error container
		}

		// confirm user doesn't exist, create one and send verification email
		public function signup ( array $reqData, ?array $reqPlaceholders = []) {

			$manager = $this->app->connection;

			//$qb = $manager->createQueryBuilder();

			$userExis = $manager->getRepository(User::class)->count( ['email'=> $reqData['email']]) > 0;

			/*if (!$userExis) // create

			else // populate error variable

			var_dump($reqData, $userExis); die();*/

			return $this->formatForEngine([['message' => 'user exists']] ) + $reqData;
		}
	}

?>