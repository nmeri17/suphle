<?php
	namespace Suphle\Bridge\Laravel;

	use Illuminate\Contracts\Debug\ExceptionHandler;

	use Throwable;

	class DefaultExceptionHandler implements ExceptionHandler {

		public function report (Throwable $exception) {

			//
		}

		public function shouldReport(Throwable $e) {

			return true;
		}

		public function render ($request, Throwable $exception) {

			throw $exception;
		}

		public function renderForConsole ($output, Throwable $exception) {

			throw $exception;
		}
	}
?>