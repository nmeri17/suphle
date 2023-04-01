<?php
	namespace Suphle\Request;

	use Suphle\Contracts\{Bridge\LaravelContainer, Requests\RequestEventsListener};

	use Suphle\Hydration\Container;

	class DefaultRequestListener implements RequestEventsListener {

		public function __construct (

			protected readonly LaravelContainer $laravelContainer,

			protected readonly PayloadStorage $payloadStorage
		) {

			//
		}

		public function handleRefreshEvent (RequestDetails $requestDetails):void {

			$this->laravelContainer->instance(

				LaravelContainer::INCOMING_REQUEST_KEY,

				$this->laravelContainer->provideRequest(

					$requestDetails, $this->payloadStorage
				)
			);
		}
	}
?>