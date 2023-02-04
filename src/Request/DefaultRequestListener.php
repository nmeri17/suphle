<?php
	namespace Suphle\Request;

	use Suphle\Contracts\{Bridge\LaravelContainer, Requests\RequestEventsListener};

	use Suphle\Hydration\Container;

	class DefaultRequestListener implements RequestEventsListener {

		public function __construct (

			//protected readonly Container $container,

			protected readonly LaravelContainer $laravelContainer,

			protected readonly PayloadStorage $payloadStorage // test that this doesn't require servc loca
		) {

			//
		}

		public function handleRefreshEvent (RequestDetails $requestDetails):void {

			$this->laravelContainer->instance(

				"request",

				$this->laravelContainer->provideRequest(

					$requestDetails, $this->payloadStorage
				)
			);
		}
	}
?>