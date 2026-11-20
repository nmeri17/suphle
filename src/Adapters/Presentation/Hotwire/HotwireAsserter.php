<?php
namespace Suphle\Adapters\Presentation\Hotwire;

use Suphle\Adapters\Presentation\Hotwire\Formats\{BaseHotwireStream, RedirectHotwireStream};

use Suphle\Testing\Proxies\Extensions\TestResponseBridge;

trait HotwireAsserter
{
    /**
     * Doesn't confirm element presence within the frame since we have no APIs for drilling through DOM trees
    */
    protected function assertStreamNode(TestResponseBridge $response, string $hotwireAction, ?string $targets = null): void
    {

        $renderer = $response->lastRenderer;
        
        if (!$renderer instanceof BaseHotwireStream) {

            $this->fail("Request not handled by ". BaseHotwireStream::class);
        }

        $foundAction = false;

        foreach ($renderer->getStreamBuilders() as $builder) {

            if ($builder->hotwireAction != $hotwireAction) {
                continue;
            }

            if (is_null($targets)) {

                $this->assertTrue(true);

                return;
            }

            if ($builder->targets == $targets) {

                $this->assertTrue(true);

                return;
            }

            $foundAction = true;
        }

        if ($foundAction) {

            $failMessage = "Found node matching action '$hotwireAction' but no corresponding target '$targets'";
        } else {

            $failMessage = "No node found in response matching action '$hotwireAction'";

            if (!is_null($targets)) {

                $failMessage .= " and target '$targets'";
            }
        }

        $this->fail($failMessage);
    }

    protected function assertHotwireRedirect(TestResponseBridge $response): void
    {

        $response->assertStatus(RedirectHotwireStream::STATUS_CODE);
    }
}
