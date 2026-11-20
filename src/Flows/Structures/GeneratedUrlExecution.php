<?php
namespace Suphle\Flows\Structures;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Routing\Structures\RouteInfo;

class GeneratedUrlExecution
{
    public function __construct(
        public string $requestPath,
        public readonly BaseRenderer $renderer,
        public readonly RouteInfo $routeInfo
    ) { }

    public function changeUrl(string $newPath): void
    {

        $this->requestPath = $newPath;
    }
}
