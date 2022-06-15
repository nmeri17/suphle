<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Contracts\Services\Decorators\ServiceErrorCatcher;

	use Tilwa\Services\Structures\BaseErrorCatcherService;

	use Tilwa\Exception\Explosives\NotFoundException;

	use Exception, InvalidArgumentException;

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

		public function deliberateException ():string {

			throw new Exception;
		}

		public function terminateRequest ():int {

			throw new InvalidArgumentException;
		}
	}
?>