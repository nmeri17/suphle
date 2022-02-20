<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Contracts\Services\Decorators\ServiceErrorCatcher;

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Exception\Explosives\NotFoundException;

	use Exception, InvalidArgumentException;

	class DatalessErrorThrower implements ServiceErrorCatcher {

		public function rethrowAs ():array {

			return [
				InvalidArgumentException::class => NotFoundException::class
			];
		}

		public function failureState (string $method):?OptionalDTO {

			if (in_array($method, [ "deliberateError", "deliberateException"]))

				return new OptionalDTO($method, false);
		}

		public function setCorrectValue (int $value):OptionalDTO {

			return new OptionalDTO ($value);
		}

		public function notCaughtInternally ():int {

			return undefinedFunction();
		}

		public function deliberateError ():string {

			trigger_error("error_msg");
		}

		public function deliberateException ():string {

			throw new Exception;
		}

		public function terminateRequest ():int {

			throw new InvalidArgumentException;
		}
	}
?>