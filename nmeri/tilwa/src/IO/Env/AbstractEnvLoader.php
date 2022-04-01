<?php
	namespace Tilwa\IO\Env;

	use Tilwa\Contracts\{IO\EnvAccessor, Config\ModuleFiles};

	use Dotenv\Dotenv;

	abstract class AbstractEnvLoader implements EnvAccessor {

		private $fileConfig;

		protected $client;

		public function __construct (ModuleFiles $fileConfig) {

			$this->fileConfig = $fileConfig;

			$this->setClient();

			$this->client->safeLoad(); // file is not expected to exist in production, as variables will be set by deployment vendor

			$this->validateFields();
		}

		public function getField (string $name) {

			return $_ENV[$name];
		}

		/**
		 * Make use of [client]
		*/
		abstract protected function validateFields ():void;

		protected function setClient ():void {

			$path = $this->fileConfig->activeModulePath();

			$this->client = Dotenv::createImmutable($path );
		}
	}
?>