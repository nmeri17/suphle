<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\{Config\Laravel as LaravelConfig, Bridge\LaravelContainer, Database\OrmDialect};

	use Tilwa\Bridge\Laravel\Config\ConfigLoader;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Request\PayloadStorage;

	use Illuminate\Http\Request;

	class Laravel implements LaravelConfig {

		private $ormDialect, $laravelContainer, $requestDetails,

		$configLoader, $payloadStorage;

		public function __construct (RequestDetails $requestDetails, OrmDialect $ormDialect, LaravelContainer $laravelContainer, ConfigLoader $configLoader, PayloadStorage $payloadStorage) {

			$this->ormDialect = $ormDialect;

			$this->laravelContainer = $laravelContainer;

			$this->configLoader = $configLoader;

			$this->requestDetails = $requestDetails;

			$this->payloadStorage = $payloadStorage;
		}

		/**
		 * {@inheritdoc}
		*/
		public function configBridge ():array {

			return [];
		}

		/**
		 * {@inheritdoc}
		*/
		public function getProviders ():array {

			return [];
		}

		/**
		 * {@inheritdoc}
		*/
		public function registersRoutes ():array {

			return [];
		} 

		/**
		 * {@inheritdoc}
		*/
		public function usesPackages ():bool {

			return false;
		}

		public function frameworkDirectory ():string {

			return  "Bridge/Laravel";
		}

		public function interfaceConcretes ():array {

			return [
				"app" => $this->laravelContainer,

				"db.connection" => $this->ormDialect->getConnection(),

				"db" => $this->ormDialect->getNativeClient(),

				"config" => $this->configLoader,

				"request" => $this->provideRequest()
			];
		}

		protected function provideRequest ():Request {

			return Request::create(
				$this->requestDetails->getPath(),

				$this->requestDetails->httpMethod(),

				$this->payloadStorage->fullPayload(),

				$_COOKIE, $_FILES, $_SERVER
			);
		}
	}
?>