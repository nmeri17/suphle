<?php
namespace Suphle\Response;

use Suphle\Modules\ModuleDescriptor;

use Suphle\Hydration\{Container, DecoratorHydrator, Structures\CallbackDetails};

use Suphle\Services\Decorators\{BindsAsSingleton, ValidationRules};

use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session, Requests\ValidationEvaluator};

use Suphle\Contracts\Response\{BaseResponseManager, RendererManager};

use Suphle\Response\Format\Redirect;

use Suphle\Request\{ PayloadStorage, RequestDetails, ValidatorManager};

use Suphle\Routing\Structures\RouteInfo;

use Suphle\Exception\Explosives\{ValidationFailure, DevError\NoCompatibleValidator};

#[BindsAsSingleton(RendererManager::class)]
class RoutedRendererManager implements RendererManager, BaseResponseManager, ValidationEvaluator
{
    use SetJsonValidationError;

    public const PREVIOUS_GET_URL = "PREVIOUS_GET_URL";

    protected array $handlerParameters;

    protected ValidatorManager $validatorManager;

    protected BaseRenderer $renderer;

    protected RouteInfo $routeDetails;

    public function __construct(
        protected readonly Container $container,
        protected readonly Session $sessionClient,
        protected readonly FlowResponseQueuer $flowQueuer,
        protected readonly RequestDetails $requestDetails,
        protected readonly CallbackDetails $callbackDetails
    ) {

        //
    }

    public function responseRenderer(): BaseRenderer
    {

        return $this->renderer;
    }

    public function afterRender($data = null): void
    {

        if (!is_null($this->routeDetails->flows)) {// the first organic request needs to trigger the flows below it

            $this->flowQueuer->saveSubBranches($this->renderer, $this->routeDetails);
        }

        if ($this->shouldStoreRenderer())

            $this->sessionClient->resetOldInput();
    }

    public function bootDefaultRenderer(): self
    {
        $this->handlerParameters = $this->fetchHandlerParameters(
            $this->routeDetails->controllerClass,

            $this->routeDetails->controllerMethod
        );
        $canaryList = $this->routeDetails->canaryInfo;

        if (!is_null($canaryList)) {

            foreach ($canaryList as $canary) {

                $loadVal = $this->container->getClass($canary)->willLoad();

                if (!is_null($loadVal))

                    $this->requestDetails->setCanaryState($loadVal);
            }
        }
        return $this;
    }

    public function handleValidRequest(PayloadStorage $payloadStorage): BaseRenderer
    {

        if ($this->shouldStoreRenderer()) {

            $this->sessionClient->setValue(
                self::PREVIOUS_GET_URL,
                $this->requestDetails->getPath()
            );
        }

        return $this->renderer = call_user_func_array(
            [$this->routeDetails->controllerClass, $this->routeDetails->controllerMethod],
            
            $this->handlerParameters
        );
    }

    protected function shouldStoreRenderer(): bool
    {
        return $this->requestDetails->isGetRequest() &&

        !$this->requestDetails->isApiRoute();
    }

    /**
     * Checks for whether current or previous should be renedered, depending on currently active renderer
     *
     * Expects current request to contain same placeholder values used for executing that preceding request
    */
    public function invokePreviousRenderer(array $toMerge = []): BaseRenderer
    {

        if (!$this->renderer->deferValidationContent()) {// if current request is something like json, write validation errors to it

            $previousRenderer = $this->renderer;

            $previousRenderer->forceArrayShape($toMerge);
        } else {

            $previousUrl = $this->sessionClient->getValue(self::PREVIOUS_GET_URL);

            $previousRenderer = new Redirect(fn () => $previousUrl);

            foreach ($toMerge as $key => $value) {

                $this->sessionClient->setFlashValue($key, $value);
            }

            $decoratorHydrator = $this->container->getClass(DecoratorHydrator::class);

            $decoratorHydrator->scopeInjecting(
                $previousRenderer,
                self::class
            );
        }

        return $previousRenderer;
    }

    public function fetchHandlerParameters(
        string $coodinator,
        string $handlingMethod
    ): array {

        return $this->container

        ->getMethodParameters($handlingMethod, $coodinator);
    }

    /**
     * {@inheritdoc}
    */
    public function mayBeInvalid(RouteInfo $routeDetails): self
    {
        $shouldValidate = $this->acquireValidatorStatus(
            
            $routeDetails->controllerClass, $routeDetails->controllerMethod
        );
        $this->routeDetails = $routeDetails;

        if ($shouldValidate) {

            if (!$this->validatorManager->isValidated()) {

                throw new ValidationFailure($this);
            }

            $this->sessionClient->resetOldInput(); // discard any validation errors
        }
        return $this;
    }

    /**
     * {@inheritdoc}
    */
    public function acquireValidatorStatus(string $coodinator, string $handlingMethod): bool
    {

        $attributesList = $this->callbackDetails->getMethodAttributes(
            $coodinator,
            $handlingMethod,
            ValidationRules::class
        );

        if ($this->eligibleToValidate(
            $coodinator,
            $handlingMethod,
            $attributesList
        )) {

            $this->validatorManager = $this->container->getClass(ValidatorManager::class); // lazily setting this since it incurs a ton of hydration irrelevant to every request

            $this->validatorManager->setActionRules(
                end($attributesList)->newInstance()->rules // use only the latest
            );

            return true;
        }

        return false;
    }

    protected function eligibleToValidate(
        string $coodinatorName,
        string $handlingMethod,
        array $attributesList
    ): bool {

        if (!empty($attributesList)) {
            return true;
        }

        if ($this->requestDetails->isGetRequest()) {
            return false;
        }

        throw new NoCompatibleValidator(
            $coodinatorName,
            $handlingMethod
        );
    }

    public function getValidatorErrors(): array
    {

        return $this->validatorManager->validationErrors();
    }

    public function validationRenderer(array $failureDetails): BaseRenderer
    {

        return $this->invokePreviousRenderer($failureDetails);
    }
}
