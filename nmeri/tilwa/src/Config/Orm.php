<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Orm as OrmConfig;

	class Orm implements OrmConfig {

		public function getCredentials ():array {

			return [
				"dbname" => getenv("DB_NAME"),

			    "user" => getenv("DB_USERNAME"),

			    "password" => getenv("DB_PASS"),

			    "driver" => "pdo_mysql",
			];
		}
	}
?>