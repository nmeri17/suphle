<?php
	namespace Suphle\Response\Format;

	use Suphle\Hydration\Structures\CallbackDetails;

	use Suphle\Services\Decorators\VariableDependencies;

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\IO\Session;

	use Suphle\Response\RoutedRendererManager;

	use Closure, Throwable;

	#[VariableDependencies(["setCallbackDetails", "setSession" ])]
	class Redirect extends GenericRenderer {

		public const STATUS_CODE = 302;

		protected CallbackDetails $callbackDetails;

		protected Session $sessionClient;

		protected int $statusCode = self::STATUS_CODE;

		/**
		 * @param {destination} Since PDO instances can't be serialized, when using this renderer with PDO in scope, wrap this parameter in a curried/doubly wrapped function
		 
		 Arguments for the eventual function are autowired and the return value is used as new request location

		 Function is bound to this object instance
		*/
		public function __construct (

			protected string $handler, protected ?Closure $destination
		) {

			//
		}

		public function setCallbackDetails (CallbackDetails $callbackDetails):void {

			$this->callbackDetails = $callbackDetails;
		}

		public function setSession (Session $sessionClient):void {

			$this->sessionClient = $sessionClient;
		}

		protected function renderRedirect (callable $callback):void {

			try { /**
				* If it's a failing form request and next destination relies on coordinator's response, renderer will have no location; so try returning back.
				* 
				* Assumes the exception's handler must have written something to session and alerter, so no need presenting exception to user here
				*/

				$nextDestination = $this->callbackDetails

				->recursiveValueDerivation($callback, $this);

				if ($nextDestination === false)

					$nextDestination = $this->navigateBack();
			}
			catch (Throwable) {

				$nextDestination = $this->navigateBack();
			}

			$this->headers[PayloadStorage::LOCATION_KEY] = $nextDestination;
		}

		protected function navigateBack ():string {

			$this->setHeaders(self::STATUS_CODE, []); // override 500 written by error handler

			return $this->sessionClient->getValue(RoutedRendererManager::PREVIOUS_GET_URL);
		}

		public function render ():string {

			$this->renderRedirect($this->destination);
			
			return "";
		}
	}
?>