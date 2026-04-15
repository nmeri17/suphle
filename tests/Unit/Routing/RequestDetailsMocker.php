<?php
namespace Suphle\Tests\Unit\Routing;

use Suphle\Contracts\Config\Router;

use Suphle\Request\RequestDetails;

use Suphle\Hydration\Container;

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

trait RequestDetailsMocker {
    use CommonBinds;

    private function stubConfig(array $stubMethods): void
    {

        $this->massProvide([

            Router::class => $this->positiveDouble(
                RouterMock::class,
                $stubMethods
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
