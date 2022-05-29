<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\{DecoratorProxy, ModuleFiles};

	use ProxyManager\{Configuration, FileLocator\FileLocator, GeneratorStrategy\FileWriterGeneratorStrategy as FileStrategy};

	class ProxyManagerConfig implements DecoratorProxy {

		protected $fileConfig;

		public function __construct (ModuleFiles $fileConfig) {

			$this->fileConfig = $fileConfig;
		}

		/**
		 * Choose a unique name that won't affect another name somewhere when used in .gitignore
		*/
		public function generatedClassesLocation ():string {

			return $this->fileConfig->activeModulePath() . "generated-proxies";
		}

		public function getConfigClient ():object {

			$classesPath = $this->generatedClassesLocation();

			$client = new Configuration;

			// generate the proxies and store them as files
			$fileLocator = new FileLocator($classesPath);

			$client->setGeneratorStrategy(new FileStrategy($fileLocator));

			$client->setProxiesTargetDir($classesPath); // set the directory to read generated proxies from

			spl_autoload_register($client->getProxyAutoloader()); // register the autoloader

			return $client;
		}
	}
?>