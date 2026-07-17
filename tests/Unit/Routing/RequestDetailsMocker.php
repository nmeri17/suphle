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

        $parameters = $this->container->getMethodParameters(Container::CLASS_CONSTRUCTOR, RequestDetails::class);

        $newRequestDetail = new class (...$parameters) extends RequestDetails {
            public static $parameters;

            public static function newRequestInstance(Container $container): RequestDetails
            {

                return new self(...self::$parameters);
            }
        };

        $newRequestDetail::$parameters = $parameters;

        $instance = $newRequestDetail::fromContainer($this->container, $url, "get");

        $instance->setIncomingVersion();

        return $instance;
    }
}
