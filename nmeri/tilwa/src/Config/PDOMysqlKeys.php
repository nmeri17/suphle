<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\{Config\Database as DatabaseContract, IO\EnvAccessor};

	class PDOMysqlKeys implements DatabaseContract {

		private $envAccessor;

		public function __construct (EnvAccessor $envAccessor) {

			$this->envAccessor = $envAccessor;
		}

		public function getCredentials ():array {

			return [
				"dbname" => $this->envAccessor->getField("DB_NAME"),

			    "user" => $this->envAccessor->getField("DB_USERNAME"),

			    "password" => $this->envAccessor->getField("DB_PASS"),

			    "driver" => "pdo_mysql",
			];
		}
	}
?>