<?php
	namespace Suphle\Config;

	use Suphle\Contracts\{Config\Database as DatabaseContract, IO\EnvAccessor};

	class PDOMysqlKeys implements DatabaseContract {

		protected ?string $parallelToken;

		public function __construct(protected readonly EnvAccessor $envAccessor) {

			$this->parallelToken = $envAccessor->getField("TEST_TOKEN");
		}

		public function getCredentials ():array {

			return [
				"default" => [

					"host" => $this->envAccessor->getField("DATABASE_HOST"),

					"database" => $this->addParallelSuffix(

						$this->envAccessor->getField("DATABASE_NAME")
					),

					"username" => $this->envAccessor->getField("DATABASE_USER"),

					"password" => $this->envAccessor->getField("DATABASE_PASS"),

					"driver" => "mysql",

					"engine" => "InnoDB"
				]
			];
		}

		public function addParallelSuffix (string $databaseName):string {

			if (is_null($this->parallelToken)) return $databaseName;

			return $databaseName. "_". $this->parallelToken;
		}
	}
?>