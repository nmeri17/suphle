<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Contracts\Services\CallInterceptors\ServiceErrorCatcher;

	use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies};

	use Suphle\Services\Structures\BaseErrorCatcherService;

	use Suphle\Exception\Explosives\NotFoundException;

	use Exception, InvalidArgumentException;

	#[InterceptsCalls]
	#[VariableDependencies([

		"setPayloadStorage", "setPlaceholderStorage"
	])]
	class DatalessErrorThrower implements ServiceErrorCatcher {

		use BaseErrorCatcherService;

		public function rethrowAs ():array {

			return [
				InvalidArgumentException::class => NotFoundException::class
			];
		}

		public function failureState (string $method) {

			if (in_array($method, [ "deliberateError", "deliberateException"]))

				return $method;
		}

		public function notCaughtInternally ():int {

			return undefinedFunction();
		}

		public function deliberateError ():string {

			trigger_error("error_msg");

			return "I'm shy";
		}

		public function deliberateException () {

			throw new Exception;
		}

		public function terminateRequest () {

			throw new InvalidArgumentException;
		}
	}
?>