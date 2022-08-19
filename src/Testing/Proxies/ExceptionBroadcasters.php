<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Contracts\Exception\{AlertAdapter, FatalShutdownAlert};

	use Exception, Throwable;

	trait ExceptionBroadcasters {

		protected function getExceptionDoubles ():array {

			return [

				FatalShutdownAlert::class => $this->positiveDouble(FatalShutdownAlert::class, [

					"setErrorAsJson" => $this->returnCallback(function ($errorDetails) {

						throw new Exception($errorDetails);
					})
				]),

				AlertAdapter::class => $this->positiveDouble(AlertAdapter::class, [

					"broadcastException" => $this->returnCallback(function (Throwable $exception, $activePayload) {

						throw $exception;
					})
				])
			];
		}
	}
?>