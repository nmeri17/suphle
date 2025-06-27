<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Contracts\Config\Router;

use Suphle\Request\RequestDetails;

use Suphle\Hydration\Container;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

use AllModules\ModuleOne\Coordinators\{
    V3\ApiV3Coordinator,
    V2\ApiV2Coordinator,
    V1\ApiV1Coordinator
};

class RequestDetailsTest extends IsolatedComponentTest
{
    use CommonBinds;

    protected bool $usesRealDecorator = false;

    protected function setUp(): void
    {

        parent::setUp();

        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                ApiV3Coordinator::class,
                ApiV2Coordinator::class,
                ApiV1Coordinator::class
            ]
        ]);
    }

    public function test_coordinator_discovery_returns_filtered_classes()
    {

        $sut = $this->getRequestDetails("api/v2/first"); // when

        $this->assertTrue($sut->isApiRoute()); // then
    }

    public function test_coordinator_discovery_handles_no_version()
    {

        $sut = $this->getRequestDetails("api/first"); // when

        $this->assertTrue($sut->isApiRoute()); // then
    }

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
