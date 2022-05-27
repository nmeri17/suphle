<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Services\Structures\DecoratorCallResult;

	use Tilwa\Exception\Explosives\Generic\UnacceptableDependency;

	class OnlyLoadedByHandler extends BaseDecoratorHandler {

		public function methodPreHooks ():array {

			return $this->allMethodAction(function (string $methodName, array $argumentList) { // object may have been called ie. higher accessor is working with results, not actual concrete

				$accessor = $this->originAccessor;

				$callDetails = $accessor->getCallDetails();

				$concrete = $callDetails->getConcrete();

				$caller = $accessor->getServiceCaller();

				foreach ($concrete->allowedConsumers() as $consumer)

					if ($caller instanceof $consumer) // can be concrete or interface

						return new DecoratorCallResult(

							$concrete// , $accessor->triggerOrigin($) // continue here
						);

				throw new UnacceptableDependency($caller, get_class($concrete));
			});
		}
	}
?>