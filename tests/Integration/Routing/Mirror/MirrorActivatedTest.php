<?php

namespace Suphle\Tests\Integration\Routing\Mirror;

use Suphle\Tests\Integration\Routing\TestsRouter;
use Suphle\Testing\Proxies\WriteOnlyContainer;
use Suphle\Contracts\Config\Router;
use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

/**
 * Tests for API route mirroring functionality
 */
class MirrorActivatedTest extends TestsRouter
{
    protected function getModules(): array
    {
        return [
            $this->replicateModule(\Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(Router::class, RouterMock::class, []);
            })
        ];
    }

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
