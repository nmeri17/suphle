<?php

namespace Suphle\Tests\Integration\Routing;

use Suphle\Routing\PathPlaceholders;

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\DefaultCollection;

class PlaceholderPatternTest extends TestsRouter
{
    protected function getEntryCollection(): string
    {

        return DefaultCollection::class; // given
    }

    public function test_placeholder_doesnt_catch_longer_path()
    {

        $matchingRenderer = $this->fakeRequest("/5");

        $this->assertNotNull($matchingRenderer); // sanity check

        $matchingRenderer = $this->fakeRequest("/5/invalid"); // when

        $this->assertNull($matchingRenderer); // then
    }

    public function test_multiple_requests_reveal_different_placeholders ()
    {

        foreach ([17, 16] as $idToSend) {

        	$this->get("/$idToSend"); // when

	        $idBeingRead = $this->getContainer()->getClass(PathPlaceholders::class)

	        ->getSegmentValue("id");

	        $this->assertEquals($idBeingRead, $idToSend); // then
	    }
    }
}
