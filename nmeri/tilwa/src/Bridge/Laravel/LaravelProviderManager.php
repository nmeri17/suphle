<?php
	namespace Tilwa\Bridge\Laravel;

	use Tilwa\Contracts\LaravelApp;

	use Tilwa\App\Container;

	use ReflectionClass;

	use Illuminate\Support\ServiceProvider;

	use Illuminate\Foundation\Application;

	class LaravelProviderManager {

		private $provider, $concrete, $laravelContainer,

		$tilwaContainer;

		function __construct (ServiceProvider $provider, LaravelApp $laravelContainer, Container $tilwaContainer) {

			$this->provider = $provider;

			$this->laravelContainer = $laravelContainer;

			$this->tilwaContainer = $tilwaContainer;
		}

		public function getConcrete():object {

			return $this->wrapConcrete();
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

		public function prepare ():self {

			return $this->extractConcrete()->mirrorBehavior();
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