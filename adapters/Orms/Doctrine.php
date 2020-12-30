<?php

	namespace Adapters\Orms;

	use Tilwa\Contracts\Orm;

	use Doctrine\ORM\Tools\Setup;

	use Doctrine\ORM\EntityManager;

	use Models\User;

	class Doctrine implements Orm {

		protected $connection;

		private $credentials;

		function __construct(array $credentials) {

			$this->credentials = $credentials;
		}

		private function setConnection ():self {

			try {

				$paths = ["Models"];

				$isDevMode = true;

				// custom edits
				$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

				$this->connection = EntityManager::create($this->credentials, $config);

				return $this;
			}
			catch (PDOException $e) {

				var_dump("unable to connect to mysql server", $e->getMessage());

				exit();
			}
		}

		public function findOne(string $model, int $id) {

			$this->getConnection()

			->getRepository($model)->find($id);
		}

		public function isModel( string $class):bool {

		    return !$this->getConnection()

		    ->getMetadataFactory()->isTransient($class);
		}

		private function getConnection () {

			if (!$this->connection) $this->setConnection(); // defer to when it's needed

			return $this->connection;
		}
	}
?>