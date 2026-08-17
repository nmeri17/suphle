<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Routing\{NamedRouteReader, AttributeRouteScanner, Structures\RouteInfo, Attributes\HttpMethod};

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

use RuntimeException, InvalidArgumentException;

class NamedRouteReaderTest extends IsolatedComponentTest {

    protected string $coordinator = BaseCoordinator::class;

    public function test_can_expand_static_route()
    {
        // given
        $url = "/segment"; // change all these cdnts, update docs

        $method = "segment";

        $reader = $this->getNamedReader($url, $method); // then

        $this->assertEquals($url, $reader->expandRoute($this->coordinator, $method)); // when
    }

    /**
    * just saves us from scanning all routes. we want to just give one route for analysis
    * @param {url} is expected to match what's on the controller
    */
    protected function getNamedReader (string $url, string $method, string $coordinator = BaseCoordinator::class, array $constrParams = []):NamedRouteReader {

        $routeInfo = new RouteInfo(
            $url, HttpMethod::GET, $coordinator, $method, [], [],

            "", null, ""
        );

        $scannerClass = AttributeRouteScanner::class;

        $routeScanner = $this->positiveDouble($scannerClass, [

            "scanModulesByPath" => [$routeInfo]
        ]);

        return $this->replaceConstructorArguments(NamedRouteReader::class, array_merge([

            $scannerClass => $routeScanner
        ], $constrParams));
    }

    public function test_can_interpolate_arguments_passed_directly()
    {
        $method = "multiPlaceholders";

        $reader = $this->getNamedReader("segment/{id}/segment/{id2}", $method); // then

        $this->assertEquals( // when
            "/segment/5/segment/profile",
            $reader->expandRoute($this->coordinator, $method, [
                "id" => 5, "id2" => "profile"
            ])
        );
    }

    public function test_can_fallback_to_path_placeholders_for_missing_arguments()
    {
        $method = "multiPlaceholders";

        $placeholders = RouteInfo::class;

        $reader = $this->getNamedReader("/segment/{id}/segment/{id2}", $method, [ // then
            $placeholders => $this->positiveDouble($placeholders, [ // given
                "getSegmentValue" => "acme-corp"
            ], [
                "getSegmentValue" => [
                    $this->once(), [$this->equalTo("id")]
                ]
            ])
        ]);

        $this->assertEquals( // when
            "/segment/acme-corp/segment/dashboard",
            $reader->expandRoute($this->coordinator, $method, [

                "id2" => "dashboard" // Didn't pass id explicitly
            ])
        );
    }

    public function test_throws_exception_if_route_missing()
    {
        $reader = $this->getNamedReader("segment", "segment"); // given

        $this->expectException(RuntimeException::class); // then

        $reader->expandRoute($this->coordinator, "missingMethod"); // when
    }

    public function test_throws_exception_if_parameter_missing()
    {
        $method = "simplePair";

        $placeholders = RouteInfo::class;

        $reader = $this->getNamedReader("segment/{id}", $coordinator, $method, [ // then
            $placeholders => $this->positiveDouble($placeholders, [

                "getSegmentValue" => null
            ])
        ]);

        $this->expectException(InvalidArgumentException::class);

        $reader->expandRoute($this->coordinator, $method); // Neither passed nor in placeholders
    }

    public function test_can_select_mirrored_route_variant()
    {
        $coordinator = "App\\Coordinators\\ProductCoordinator";
        $method = "show";

        $normalRoute = new RouteInfo(
            "/products/{id}", HttpMethod::GET, $coordinator, $method, [], [],

            "", null, "", false
        );

        $mirrorRoute = new RouteInfo(
            "/api/v1/products/{id}", HttpMethod::GET, $coordinator, $method, [], [],

            "", null, "", true
        );

        $scannerClass = AttributeRouteScanner::class;

        $routeScanner = $this->positiveDouble($scannerClass, [

            "scanModulesByPath" => [$normalRoute, $mirrorRoute]
        ]);

        $reader = $this->replaceConstructorArguments(NamedRouteReader::class, [

            $scannerClass => $routeScanner
        ]);

        $this->assertEquals(
            "/api/v1/products/5",
            $reader->expandRoute($coordinator, $method, ["id" => 5], true)
        );

        $this->assertEquals(
            "/products/5",
            $reader->expandRoute($coordinator, $method, ["id" => 5])
        );
    }
}