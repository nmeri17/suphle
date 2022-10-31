<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\DecoratorProxy;

	use Suphle\Services\ComponentEntry;

	use ProxyManager\{Configuration, FileLocator\FileLocator, GeneratorStrategy\FileWriterGeneratorStrategy as FileStrategy};

	class ProxyManagerConfig implements DecoratorProxy {

		public function __construct(protected ComponentEntry $componentEntry)
  {
  }

		protected function generatedClassesLocation ():string {

			return $this->componentEntry->userLandMirror() .

			".generated-proxies"; // user will have no need to rename this, so we've taken the liberty of creating a folder with this name to be copied to user-land
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