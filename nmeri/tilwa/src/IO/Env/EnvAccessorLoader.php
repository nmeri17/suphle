<?php
	namespace Tilwa\IO\Env;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\Config\ModuleFiles;

	use Dotenv\Dotenv;

	class EnvAccessorLoader extends BaseInterfaceLoader {

		private $fileConfig;

		public function __construct (ModuleFiles $fileConfig) {

			$this->fileConfig = $fileConfig;
		}

		public function concrete ():string {

			return EnvLoaderConcrete::class;
		}

		protected function afterBind ($initialized):void {		

			Dotenv::createImmutable( $this->fileConfig->activeModulePath())

			->load();
		}
	}
?>