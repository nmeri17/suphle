<?php
	namespace Suphle\Auth;

	use Suphle\Hydration\{BaseInterfaceLoader, Container};

	use Suphle\Contracts\Config\AuthContract;

	class LoginHandlerInterfaceLoader extends BaseInterfaceLoader {

		public function __construct(private readonly Container $container, private readonly AuthContract $authContract)
  {
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