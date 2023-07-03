<?php

namespace Suphle\Response;

use Suphle\Modules\ModuleDescriptor;

use Suphle\Hydration\{Container, DecoratorHydrator, Structures\CallbackDetails};

use Suphle\Services\ServiceCoordinator;

use Suphle\Services\Decorators\{BindsAsSingleton, ValidationRules};

use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session, Requests\ValidationEvaluator};

use Suphle\Contracts\Response\{BaseResponseManager, RendererManager};

use Suphle\Response\Format\Redirect;

use Suphle\Request\{ PayloadStorage, RequestDetails, ValidatorManager};

use Suphle\Exception\Explosives\{ValidationFailure, DevError\NoCompatibleValidator};

#[BindsAsSingleton(RendererManager::class)]
class RoutedRendererManager implements RendererManager, BaseResponseManager, ValidationEvaluator
{
    use SetJsonValidationError;

    public const PREVIOUS_GET_URL = "PREVIOUS_GET_URL";

    protected array $handlerParameters;

    protected ValidatorManager $validatorManager;

    public function __construct(
        protected readonly Container $container,
        protected readonly BaseRenderer $renderer,
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

        if ($this->renderer->hasBranches()) {// the first organic request needs to trigger the flows below it

            $this->flowQueuer->saveSubBranches($this->renderer);
        }

        if ($this->shouldStoreRenderer())

        	$this->sessionClient->resetOldInput();
    }

    public function bootDefaultRenderer(): self
    {

        $this->handlerParameters = $this->fetchHandlerParameters(
            $this->renderer->getCoordinator(),
            $this->renderer->getHandler()
        );

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

        return $this->renderer->invokeActionHandler($this->handlerParameters);
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

            $previousRenderer = new Redirect("", fn () => $previousUrl);

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
        ServiceCoordinator $coodinator,
        string $handlingMethod
    ): array {

        return $this->container

        ->getMethodParameters($handlingMethod, $coodinator::class);
    }

    /**
     * {@inheritdoc}
    */
    public function mayBeInvalid(?BaseRenderer $renderer = null): self
    {

        if (is_null($renderer)) {
            $renderer = $this->renderer;
        }

        $shouldValidate = $this->acquireValidatorStatus(
            $renderer->getCoordinator(),
            $renderer->getHandler()
        );

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
    public function acquireValidatorStatus(ServiceCoordinator $coodinator, string $handlingMethod): bool
    {

        $attributesList = $this->callbackDetails->getMethodAttributes(
            $coodinator::class,
            $handlingMethod,
            ValidationRules::class
        );

        if ($this->eligibleToValidate(
            $coodinator::class,
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
