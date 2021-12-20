<?php
	namespace Tilwa\Hydration;

	use Tilwa\Hydration\Templates\ProvidedServiceWrapper;

	use Tilwa\Contracts\{LaravelApp, Config\Laravel as LaravelConfig};

	use ReflectionClass;

	use Illuminate\{Support\ServiceProvider, Foundation\Application};

	class LaravelProviderManager {

		private $provider, $concrete, $laravelContainer,

		$config, $tilwaContainer;

		public function __construct ( LaravelApp $laravelContainer, LaravelConfig $config, Container $tilwaContainer) {

			$this->laravelContainer = $laravelContainer;

			$this->tilwaContainer = $tilwaContainer;

			$this->config = $config;
		}

		public function setActiveProvider (string $provider):void {

			$this->provider = new $provider($this->laravelContainer);
		}

		public function canProvide (string $fullName):bool {

			$config = $this->config;

			return $config->usesPackages() && array_key_exists( $fullName, $config->getProviders() );
		}

		public function manageService (string $fullName):ProvidedServiceWrapper {

			$providerName = $this->config->getProviders()[$fullName];

			$this->setActiveProvider($providerName);

			return $this->extractConcrete()

			->mirrorBehavior()

			->wrapConcrete();
		}

		private function mirrorBehavior ():self {

			require_once $this->getHelperFilePath(); // we need this file active while running their routes so it can pick [view()]

			function app () { // override their definition

				return $this->laravelContainer;
			}
			
			$this->provider->boot();
		
			return $this;
		}

		private function extractConcrete ():self {

			$currentBindings = $this->laravelContainer->getBindings();

			$this->provider->register();

			$newBindings = $this->laravelContainer->getBindings();

			$latestKey = array_diff_key($currentBindings, $newBindings);

			$this->concrete = $newBindings[current($latestKey)]["concrete"]();

			return $this;
		}

		// use a known class within that namespace to pull the file's directory
		private function getHelperFilePath ():string {

			$knownClass = new ReflectionClass(Application::class);

			$namespaces = explode("\\", $knownClass->getFileName(), -1);

			$namespaces[] = "helpers.php";

			return implode(DIRECTORY_SEPARATOR, $namespaces);
		}

		private function wrapConcrete ():ProvidedServiceWrapper {

			return $this->tilwaContainer

			->genericFactory(
				ProvidedServiceWrapper::class,

				["target" => get_class($this->concrete)],

				function ($types) {

				    return new ProvidedServiceWrapper($this->concrete, $this->getHelperFilePath ());
				}
			);
		}
	}
?>