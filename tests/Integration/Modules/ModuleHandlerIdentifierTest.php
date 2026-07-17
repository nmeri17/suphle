<?php

namespace Suphle\Tests\Integration\Modules;

use Suphle\Contracts\Config\{Router as RouterContract, Auth as AuthContract};

use Suphle\Flows\OuterFlowWrapper;

use Suphle\Config\{Auth, Router};

use Suphle\Testing\{Condiments\DirectHttpTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor};

class ModuleHandlerIdentifierTest extends JobFactory
{
    use DirectHttpTest, DoublesHandlerIdentifier;

    protected function setUp(): void
    {

        $this->setDummyRenderer();

        parent::setUp();
    }

    // no need to create these. We're not interested in using any
    protected function setAllDescriptors(): void
    {
    }

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "browserEntryRoute" => OriginCollection::class
                ]);
            })
        ];
    }

    public function test_saved_flow_triggers_flow_handler()
    {

        $this->handleDefaultPendingFlowDetails(); // given

        //$this->assertHandledByFlow($this->userUrl);

        $this->setHttpParams($this->userUrl); // when

        $this->getHandlerIdentifier([], [

            "flowRequestHandler" => [$this->atLeastOnce(), [ // then

                $this->callback(fn ($argument) => is_a($argument, OuterFlowWrapper::class))
            ]]
        ])
        ->respondFromHandler();
    }
}
