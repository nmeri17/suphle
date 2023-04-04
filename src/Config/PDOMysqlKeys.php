<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\{Database as DatabaseContract, ModuleFiles};

	use Suphle\Contracts\IO\EnvAccessor;

	class PDOMysqlKeys implements DatabaseContract {

		protected ?string $parallelToken;

		protected string $relativeFolderName = "AppModels";

		public function __construct (

			protected readonly EnvAccessor $envAccessor,

			protected readonly ModuleFiles $fileConfig
		) {

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

		protected function addParallelSuffix (string $databaseName):string {

			return is_null($this->parallelToken) ? $databaseName:

			$databaseName. "_". $this->parallelToken;
		}

		/**
		 * {@inheritdoc}
		*/
		public function componentInstallPath ():string {

			return $this->fileConfig->getRootPath().

			$this->relativeFolderName . DIRECTORY_SEPARATOR;
		}

		public function componentInstallNamespace ():string {

			return $this->relativeFolderName;
		}
	}
?>