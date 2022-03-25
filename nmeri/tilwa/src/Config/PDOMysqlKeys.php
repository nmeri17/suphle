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
				"default" => [

					"host" => $this->envAccessor->getField("DATABASE_HOST"),

					"database" => $this->envAccessor->getField("DATABASE_NAME"),

					"username" => $this->envAccessor->getField("DATABASE_USER"),

					"password" => $this->envAccessor->getField("DATABASE_PASS"),

					"driver" => "mysql"
				]
			];
		}
	}
?>