<?php

	namespace Tilwa\Adapters\Orms;

	use Tilwa\Contracts\Orm;

	use Doctrine\ORM\Tools\Setup;

	use Doctrine\ORM\EntityManager;

	class Doctrine implements Orm {

		private $credentials, $connection;

		function __construct(OrmConfig $config) {

			$this->credentials = $config->getCredentials();
		}

		private function setConnection ():void {

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

		public function getConnection () {

			if (!$this->connection) $this->setConnection(); // defer to when it's needed

			return $this->connection;
		}
	}
?>