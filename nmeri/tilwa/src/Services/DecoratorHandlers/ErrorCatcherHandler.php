<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Services\Structures\DecoratorCallResult;

	use Throwable;

	class ErrorCatcherHandler extends BaseDecoratorHandler {

		public function methodPreHooks ():array {

			return $this->allMethodAction(function (string $methodName, array $argumentList) {

				try {

					$result = $this->originAccessor->triggerOrigin($methodName, $argumentList);
				}
				catch (Throwable $exception) {

					$result = $this->originAccessor->attemptDiffuse($exception, $methodName);
				}

				$concrete = $this->originAccessor->getCallDetails()->getConcrete();
				
				return new DecoratorCallResult($concrete, $result, true);
			});
		}
	}
?>