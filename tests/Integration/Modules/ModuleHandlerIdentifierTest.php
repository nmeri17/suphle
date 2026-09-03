<?php

namespace Suphle\Tests\Integration\Modules;

use Suphle\Modules\ModuleHandlerIdentifier;

use Suphle\Flows\OuterFlowWrapper;

use Suphle\Testing\{Condiments\DirectHttpTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

class ModuleHandlerIdentifierTest extends JobFactory
{
    use DirectHttpTest;

    public function test_saved_flow_triggers_flow_handler()
    {

        $sut = $this->getHandlerIdentifier();

        $this->handleDefaultPendingFlowDetails(); // given

        $postsUrl = "/posts/5";

        $this->setHttpParams($postsUrl); // when

        $sut->respondFromHandler(); // then

        $this->assertHandledByFlow($postsUrl); // if this fails, the stub prevented it from getting to that point so delete
    }

    protected function getHandlerIdentifier(): ModuleHandlerIdentifier
    {

        $identifier = $this->replaceConstructorArguments(
            ModuleHandlerIdentifier::class,
            [], [

                "getModules" => $this->modules,
            ], [

                "flowRequestHandler" => [$this->atLeastOnce(), [ // then

                    $this->callback(fn ($argument) => is_a($argument, OuterFlowWrapper::class))
                ]]
            ],
            true, true, true
        );

        return $identifier;
    }
}
