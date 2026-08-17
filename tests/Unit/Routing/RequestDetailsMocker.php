<?php
namespace Suphle\Tests\Unit\Routing;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Request\RequestDetails;

use Suphle\Hydration\Container;

use Suphle\Tests\Integration\Generic\CommonBinds;

trait RequestDetailsMocker {
    use CommonBinds;

    private function stubConfig(array $stubMethods): void
    {

        $this->massProvide([

            RouterContract::class => $this->positiveDouble(
                Router::class, $stubMethods
            )
        ]);
    }

    private function getRequestDetails(string $url): RequestDetails
    {
        return RequestDetails::fromContainer($this->container, $url, "get");
    }
}
