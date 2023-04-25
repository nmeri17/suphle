<?php

namespace Suphle\Response;

use Suphle\Contracts\Presentation\RendersMarkup;

/**
 * Used by library-facing consumers (diffusers for eg), that replace the template of given markup renderer
*/
trait ModifiesRendererTemplate
{
    protected function setMarkupDetails(): void
    {

        if (!$this->renderer instanceof RendersMarkup) {
            return;
        }

        $this->renderer->setMarkupName($this->newMarkupName);

        $this->htmlParser->findInPath(
            $this->componentEntry->userLandMirror() . "Markup".

            DIRECTORY_SEPARATOR
        );
    }
}
