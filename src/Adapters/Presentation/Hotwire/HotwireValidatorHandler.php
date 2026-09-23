<?php
namespace Suphle\Adapters\Presentation\Hotwire;

use Suphle\Middleware\ValidationRulesHandler;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Adapters\Presentation\Hotwire\Formats\RedirectHotwireStream;

class HotwireValidatorHandler extends ValidationRulesHandler {

    public function validationRenderer(array $failureDetails): BaseRenderer
    {        
        // The fallback is a redirect to the previous form if Hotwire fails or needs a clean reload
        $errorRenderer = new RedirectHotwireStream(fn () => $this->rendererManager->getPreviousUrl());

        $this->scopeRenderer($errorRenderer);

        if (!$errorRenderer->isHotwireRequest()) {

            return $this->rendererManager->invokePreviousRenderer($failureDetails);
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
