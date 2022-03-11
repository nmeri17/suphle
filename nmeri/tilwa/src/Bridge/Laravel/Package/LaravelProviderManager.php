<?php
	namespace Tilwa\Bridge\Laravel\Package;

	use Tilwa\Hydration\Container;

	use Tilwa\Bridge\Laravel\Package\Templates\ProvidedServiceWrapper;

	use Tilwa\Contracts\{Bridge\LaravelContainer, Config\Laravel as LaravelConfig};

	use ReflectionClass;

	use Illuminate\{Support\ServiceProvider, Foundation\Application};

	class LaravelProviderManager {

		private $provider, $concrete, $laravelContainer,

		$config, $tilwaContainer, $helperFilePath;

		public function __construct ( LaravelContainer $laravelContainer, LaravelConfig $config, Container $tilwaContainer) {

			$this->laravelContainer = $laravelContainer;

			$this->tilwaContainer = $tilwaContainer;

			$this->config = $config;
		}

		public function setActiveProvider (string $provider):void {

			$this->provider = new $provider($this->laravelContainer);
		}
		
		public function getActiveProvider ():ServiceProvider {

			return $this->provider;
		}

		public function canProvide (string $fullName):bool {

			$config = $this->config;

			return $config->usesPackages() && array_key_exists( $fullName, $config->getProviders() );
		}

		public function manageService (string $fullName):ProvidedServiceWrapper {

			$providerName = $this->config->getProviders()[$fullName];

			$this->setActiveProvider($providerName);

			return $this->createSandbox(function () {

				$this->extractConcrete();
			
				$this->provider->boot();

				return $this->wrapConcrete();
			});
		}

		private function extractConcrete ():void {

			$currentBindings = $this->laravelContainer->getBindings();

			$this->provider->register();

			$newBindings = $this->laravelContainer->getBindings();

			$latestKey = array_diff_key($currentBindings, $newBindings);

			$this->concrete = $newBindings[current($latestKey)]["concrete"]();
		}

		// use a known class within that namespace to pull the file's directory
		private function getHelperFilePath ():string {

			if (!is_null($this->helperFilePath))

				return $this->helperFilePath;

			$this->setHelperFilePath();

			return $this->helperFilePath;
		}

		public function createSandbox (callable $explosive) {

			require_once $this->getHelperFilePath(); // we need this file active while running their routes so it can pick [view()]

			function app () { // override their definition

				return $this->laravelContainer;
			}

			return $explosive();
		}

		private function setHelperFilePath ():void {

			$knownClass = new ReflectionClass(Application::class);

			$namespaces = explode("\\", $knownClass->getFileName(), -1);

			$namespaces[] = "helpers.php";

			$this->helperFilePath = implode(DIRECTORY_SEPARATOR, $namespaces);
		}

		private function wrapConcrete ():ProvidedServiceWrapper {

			return $this->tilwaContainer

			->genericFactory(
				ProvidedServiceWrapper::class,

				["target" => get_class($this->concrete)],

				function ($types) {

				    return new ProvidedServiceWrapper($this->concrete, $this);
				}
			);
		}
	}
?>