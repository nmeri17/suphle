<?php

namespace Suphle\Adapters\Presentation\Hotwire;

use Suphle\Hydration\{Container, Structures\CallbackDetails};

use Suphle\Contracts\{IO\Session, Requests\ValidationFailureConvention, Presentation\BaseRenderer};

use Suphle\Response\{FlowResponseQueuer, RoutedRendererManager};

use Suphle\Request\{ValidatorManager, RequestDetails};

use Suphle\Adapters\Presentation\Hotwire\Formats\BaseHotwireStream;

use Suphle\Services\ServiceCoordinator;

use Suphle\Exception\Explosives\ValidationFailure;

class HotwireRendererManager extends RoutedRendererManager
{
    public function __construct(
        protected readonly Container $container,
        protected readonly BaseRenderer $renderer,
        protected readonly Session $sessionClient,
        protected readonly FlowResponseQueuer $flowQueuer,
        protected readonly RequestDetails $requestDetails,
        protected readonly CallbackDetails $callbackDetails,
        protected readonly ValidationFailureConvention $failureConvention
    ) {

        //
    }

    public function bootDefaultRenderer(): self
    {

        if ($this->avoidHotwireConditions()) {

            return parent::bootDefaultRenderer();
        }

        foreach ($this->renderer->getHotwireHandlers() as [, $handler]) {

            $this->handlerParameters[] = $this->fetchHandlerParameters(
                $this->renderer->getCoordinator(),
                $handler
            );
        }

        return $this;
    }

    protected function avoidHotwireConditions(): bool
    {

        return !($this->renderer instanceof BaseHotwireStream) ||

        !$this->renderer->isHotwireRequest();
    }

    public function validationRenderer(array $failureDetails): BaseRenderer
    {

        if ($this->avoidHotwireConditions()) {

            return $this->invokePreviousRenderer($failureDetails);
        }

        return $this->failureConvention

        ->deriveFormPartial($this->renderer, $failureDetails);
    }

    /**
     * {@inheritdoc}
    */
    public function mayBeInvalid(?BaseRenderer $renderer = null): self
    {

        if (is_null($renderer)) {
            $renderer = $this->renderer;
        }

        if ($this->avoidHotwireConditions()) {

            return parent::mayBeInvalid($renderer);
        }

        foreach ($renderer->getHotwireHandlers() as [, $handler]) {

            $shouldValidate = $this->acquireValidatorStatus(
                $renderer->getCoordinator(),
                $handler
            );

            if ($shouldValidate && !$this->validatorManager->isValidated()) {

                throw new ValidationFailure($this);
            }
        }

        return $this;
    }

    public function shouldSetCode(RequestDetails $requestDetails, BaseRenderer $renderer): bool
    {

        $isHotwireRenderer = !$this->avoidHotwireConditions();

        return $isHotwireRenderer ||

        parent::shouldSetCode($requestDetails, $renderer);
    }
}
