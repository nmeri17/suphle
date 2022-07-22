<?php
	namespace Suphle\Auth;

	use Suphle\Hydration\{BaseInterfaceLoader, Container};

	use Suphle\Contracts\Config\AuthContract;

	class LoginHandlerInterfaceLoader extends BaseInterfaceLoader {

		private $container, $authContract;

		public function __construct (Container $container, AuthContract $authContract) {

			$this->container = $container;

			$this->authContract = $authContract;
		}

		public function concreteName ():string {

			return LoginRequestHandler::class;
		}

		public function bindArguments ():array {

			return [

				"collection" => $this->container->getClass($this->authContract->getLoginCollection()) // passing collection as argument so the handler can receive a type-safe object
			];
		}
	}
?>