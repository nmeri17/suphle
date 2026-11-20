<?php
namespace Suphle\Response\Format;

use Suphle\Hydration\Structures\CallbackDetails;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Request\{PayloadStorage, RequestDetails};

use Suphle\Contracts\Response\RendererManager;

use Closure, Throwable;

#[VariableDependencies(["setCallbackDetails", "setRendererManager", "setRequestDetails" ])]
class Redirect extends GenericRenderer {

    public const STATUS_CODE = 302;

    protected CallbackDetails $callbackDetails;

    protected RendererManager $rendererManager;

    protected RequestDetails $requestDetails;

    protected int $statusCode = self::STATUS_CODE;

    /**
     * @param destination Since PDO instances can't be serialized, when using this renderer with PDO in scope, wrap this parameter in a curried/doubly wrapped function
     * 
     * Arguments for the eventual function are autowired and the return value is used as new request location
     * Function is bound to this object instance
     */
    public function __construct(
        protected ?Closure $destination
    ) { }

    public function setCallbackDetails(CallbackDetails $callbackDetails): void
    {
        $this->callbackDetails = $callbackDetails;
    }

    public function setRendererManager(RendererManager $rendererManager): void
    {
        $this->rendererManager = $rendererManager;
    }

    public function setRequestDetails(RequestDetails $requestDetails): void {

        $this->requestDetails = $requestDetails;
    }

    protected function renderRedirect(callable $callback): void
    {
        try {
            /**
             * If it's a failing form request and next destination relies on coordinator's response, renderer will have no location; so try returning back.
             *
             * Assumes the exception's handler must have written something to session and alerter, so no need presenting exception to user here
             */
            $nextDestination = $this->callbackDetails
                ->recursiveValueDerivation($callback, $this);

            if ($nextDestination === false) {
                $nextDestination = $this->navigateBack();
            }
        } catch (Throwable $exception) {
            var_dump($exception->getMessage());

            $nextDestination = $this->navigateBack();
        }

        $this->headers[PayloadStorage::LOCATION_KEY] = $nextDestination;
    }

    protected function navigateBack(): string
    {
        $this->setHeaders(self::STATUS_CODE, []); // override 500 written by error handler

        return $this->rendererManager->getPreviousUrl();
    }

    public function render(): string
    {
        $this->renderRedirect($this->destination);

        $routeInfo = $this->requestDetails->getComputedDetails();

        if (!$routeInfo?->isMirror) return "";

        $this->rawResponse = [

            "redirect" => $this->headers[PayloadStorage::LOCATION_KEY] ?? null
        ];

        $this->setHeaders(Json::STATUS_CODE, $routeInfo->mirrorHeader);

        return $this->renderJson();        
    }
}
