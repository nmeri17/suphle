<?php
	namespace Suphle\Config;

	use Suphle\Contracts\{Config\Database as DatabaseContract, IO\EnvAccessor};

	class PDOMysqlKeys implements DatabaseContract {

		public function __construct(protected readonly EnvAccessor $envAccessor) {

			//
		}

		public function getCredentials ():array {

			return [
				"default" => [

					"host" => $this->envAccessor->getField("DATABASE_HOST"),

					"database" => $this->envAccessor->getField("DATABASE_NAME"),

					"username" => $this->envAccessor->getField("DATABASE_USER"),

					"password" => $this->envAccessor->getField("DATABASE_PASS"),

					"driver" => "mysql",

					"engine" => "InnoDB"
				]
			];
		}
	}
?>