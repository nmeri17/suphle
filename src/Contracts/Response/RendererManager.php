<?php
namespace Suphle\Contracts\Response;

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\Presentation\BaseRenderer;

interface RendererManager
{
    public function handleValidRequest(PayloadStorage $payloadStorage): BaseRenderer;

    public function invokePreviousRenderer(array $toMerge = []): BaseRenderer;
}
