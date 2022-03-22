<?php
	namespace Tilwa\Auth;

	use Tilwa\Hydration\{BaseInterfaceLoader, Container};

	use Tilwa\Contracts\Config\Auth as AuthConfig;

	class LoginHandlerInterfaceLoader extends BaseInterfaceLoader {

		private $container, $authConfig;

		public function __construct (Container $container, AuthConfig $authConfig) {

			$this->container = $container;

			$this->authConfig = $authConfig;
		}

		public function concrete ():string {

			return LoginRequestHandler::class;
		}

		public function bindArguments ():array {

			return [

				"collection" => $this->container->getClass($this->authConfig->getLoginCollection())
			];
		}
	}
?>