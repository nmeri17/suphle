<?php

namespace Suphle\Tests\Integration\Routing;

use Suphle\Routing\Structures\RouteInfo;

class RouteInfoTest extends TestsRouter
{
    /**
     * @dataProvider pathsAndPlaceholders
     */
    public function test_replaceInPattern(string $activePath, array $expectedPlaceholders)
    {

        $matchingRouteInfo = $this->findRouteInfo($activePath);

        $this->assertSame(
            $expectedPlaceholders,
            $matchingRouteInfo->getAllSegmentValues()
        ); // then
    }

    public function test_can_extract_all_method_segments()
    {

        $segments = RouteInfo::extractPlaceholders("segment/{id}/segment2/{id2}/"); // when

        $this->assertSame(["id", "id2"], $segments); // then
    }

    public function test_placeholder_doesnt_catch_longer_path()
    {

        $matchingRouteInfo = $this->findRouteInfo("/5");

        $this->assertNotNull($matchingRouteInfo); // sanity check

        $matchingRouteInfo = $this->findRouteInfo("/5/invalid"); // when

        $this->assertNull($matchingRouteInfo); // then
    }

    public function test_multiple_requests_reveal_different_placeholders ()
    {

        foreach ([17, 16] as $idToSend) {

            $this->get("/$idToSend"); // when

            $idBeingRead = $this->getContainer()->getClass(RouteInfo::class)

            ->getSegmentValue("id");

            $this->assertEquals($idBeingRead, $idToSend); // then
        }
    }
}
