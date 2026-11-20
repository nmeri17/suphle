<?php
namespace Suphle\Adapters\Presentation\Hotwire;

use Suphle\Hydration\{Container, Structures\CallbackDetails};

use Suphle\Routing\Structures\RouteInfo;

use Suphle\Contracts\{Requests\ValidationEvaluator, Presentation\BaseRenderer, IO\Session, Response\RendererManager};

use Suphle\Request\{PayloadStorage, RequestDetails, ValidatorManager, ValidationRulesHandler};

use Suphle\Adapters\Presentation\Hotwire\Formats\RedirectHotwireStream;

class HotwireValidatorHandler extends ValidationRulesHandler {

    public function __construct(
        CallbackDetails $callbackDetails, 
        RequestDetails $requestDetails, 
        Container $container,
        Session $sessionClient,
        RendererManager $rendererManager,
        protected readonly PayloadStorage $payloadStorage
    ) {
        parent::__construct($callbackDetails, $requestDetails, $container, $sessionClient, $rendererManager);
    }

    public function validationRenderer(array $failureDetails): BaseRenderer
    {        
        // The fallback is a redirect to the previous form if Hotwire fails or needs a clean reload
        $prevUrl = $this->rendererManager->getPreviousUrl();

        $errorRenderer = new RedirectHotwireStream(fn () => $prevUrl);

        $this->rendererManager->scopeRenderer($errorRenderer);

        if (!$errorRenderer->isHotwireRequest()) {

            return $this->rendererManager->rendererDataOnError($failureDetails);
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
}
