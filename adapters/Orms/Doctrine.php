<?php

	namespace Adapters\Orms;

	use Tilwa\Contracts\Orm;

	use Doctrine\ORM\Tools\Setup;

	use Doctrine\ORM\EntityManager;

	use Models\User;

	class Doctrine implements Orm {

		public function setConnection () {

			try {

				$connectionParams = [
					'dbname' => getenv('DB_PROD'),

				    'user' => getenv('DB_USER'),

				    'password' => getenv('DB_PASS'),

				    'driver' => 'pdo_mysql',
				];

				$paths = ["models"];

				$isDevMode = true;

				// custom edits
				$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

				$entityManager = EntityManager::create($connectionParams, $config);

				$this->connection = $entityManager;

				return $this;
			}
			catch (PDOException $e) {

				var_dump("unable to connect to mysql server", $e->getMessage());

				exit();
			}
		}

		public function getUser() {

			$user = null; // guest

			if ($userId = @$_SESSION['tilwa_user_id'])

				$user = $this->connection

				->getRepository(User::class)

				->find($userId);

			return $user;
		}
	}
?>