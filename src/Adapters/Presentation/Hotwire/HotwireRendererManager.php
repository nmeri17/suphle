<?php

namespace Suphle\Adapters\Presentation\Hotwire;

use Suphle\Hydration\{Container, DecoratorHydrator, Structures\CallbackDetails};

use Suphle\Contracts\{IO\Session, Presentation\BaseRenderer};

use Suphle\Response\{FlowResponseQueuer, RoutedRendererManager};

use Suphle\Request\{ValidatorManager, RequestDetails, PayloadStorage};

use Suphle\Adapters\Presentation\Hotwire\Formats\{BaseHotwireStream, RedirectHotwireStream};

use Suphle\Services\BaseCoordinator;

use Suphle\Exception\Explosives\ValidationFailure;

class HotwireRendererManager extends RoutedRendererManager
{
    public function __construct(
        protected readonly Container $container,
        protected readonly Session $sessionClient,
        protected readonly FlowResponseQueuer $flowQueuer,
        protected readonly RequestDetails $requestDetails,
        protected readonly CallbackDetails $callbackDetails,
        protected readonly PayloadStorage $payloadStorage
    ) {

        //
    }

    public function validationRenderer(array $failureDetails): BaseRenderer
    {
        $previousUrl = $this->sessionClient->getValue(self::PREVIOUS_GET_URL);
        
        // The fallback is a redirect to the previous form if Hotwire fails or needs a clean reload
        $errorRenderer = new RedirectHotwireStream(fn () => $previousUrl);

        $decoratorHydrator = $this->container->getClass(DecoratorHydrator::class);
        
        $decoratorHydrator->scopeInjecting($errorRenderer, self::class);

        if (!$errorRenderer->isHotwireRequest()) {

            return $this->invokePreviousRenderer($failureDetails);
        }

        $domTarget = "_turbo_target";

        $targetContainer = $this->payloadStorage->keyHasContent($domTarget)?

        $this->payloadStorage->getKey($domTarget): "#form-container";

        $errorRenderer->addReplace(
            $failureDetails, // The data source payload (errors + old input)
            
            fn ($result) => $targetContainer, // The target container to dump the error markup into
            "hotwire/form-fragment" // The UI partial designed to read and display errors
        );

        return $errorRenderer;
    }

    public function shouldSetCode(RequestDetails $requestDetails, BaseRenderer $renderer): bool
    {
        $isHotwireRenderer = $renderer instanceof BaseHotwireStream && $renderer->isHotwireRequest();

        return $isHotwireRenderer ||

        parent::shouldSetCode($requestDetails, $renderer);
    }
}
