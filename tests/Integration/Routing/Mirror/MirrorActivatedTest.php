<?php

namespace Suphle\Tests\Integration\Routing\Mirror;

use Suphle\Tests\Integration\Routing\TestsRouter;

/**
 * As with other configs on [TestsRouter], the apiStack is pulled from [ModuleOne]
*/
class MirrorActivatedTest extends TestsRouter
{
    public function test_can_switch_to_api_collection()
    {

        $matchingRenderer = $this->fakeRequest("/api/v1/api-segment"); // when

        $this->assertNotNull($matchingRenderer);

        $this->assertTrue($matchingRenderer->matchesHandler("segmentHandler")); // then
    }

    public function test_can_detect_browser_route()
    {

        $matchingRenderer = $this->fakeRequest("/api/v1/segment"); // when

        $this->assertNotNull($matchingRenderer);

        $this->assertTrue($matchingRenderer->matchesHandler("plainSegment")); // then
    }

    public function test_can_override_browser_route()
    {

        $matchingRenderer = $this->fakeRequest("/api/v1/segment/5"); // when

        $this->assertNotNull($matchingRenderer);

        $this->assertTrue($matchingRenderer->matchesHandler("simplePairOverride")); // then
    }
}
