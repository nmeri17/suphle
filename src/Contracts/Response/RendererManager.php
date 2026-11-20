<?php
namespace Suphle\Contracts\Response;

use Suphle\Contracts\Presentation\BaseRenderer;

interface RendererManager
{
    public function handleValidRequest(): BaseRenderer;

    public function rendererDataOnError(array $toMerge = []): BaseRenderer;

    public function fallbackShouldJson ():bool;

    public function scopeRenderer (BaseRenderer $renderer):void;
}
