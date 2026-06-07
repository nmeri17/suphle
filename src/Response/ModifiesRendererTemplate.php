<?php
namespace Suphle\Response;

/**
 * Used by library-facing consumers (diffusers for eg), that replace the template of given markup renderer
*/
trait ModifiesRendererTemplate
{
    protected function setMarkupDetails(): void
    {
        $this->htmlParser->findInPath(
            $this->componentEntry->userLandMirror() . "Markup".

            DIRECTORY_SEPARATOR
        );
    }
}
