<?php

namespace Suphle\Tests\Integration\Modules;

use Suphle\Contracts\Auth\{ModuleLoginHandler, LoginFlowMediator};

use Suphle\Contracts\Config\{Router, AuthContract};

use Suphle\Flows\OuterFlowWrapper;

use Suphle\Exception\Explosives\ValidationFailure;

use Suphle\Config\Auth;

use Suphle\Testing\{Condiments\DirectHttpTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

class ModuleHandlerIdentifierTest extends JobFactory
{
    use DirectHttpTest;
    use DoublesHandlerIdentifier;

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

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "browserEntryRoute" => OriginCollection::class
                ]);
            })
        ];
    }

    public function test_can_handle_login()
    {

        $this->massProvide([

            ModuleLoginHandler::class => $this->mockLoginHandler() // then
        ]);

        $this->post(
            Auth::API_LOGIN_PATH // given
        ); // when
    }

    private function mockLoginHandler(): ModuleLoginHandler
    {

        return $this->positiveDouble(
            ModuleLoginHandler::class,
            [

                "isValidRequest" => true,

                "handlingRenderer" => $this->dummyRenderer,

                "setResponseRenderer" => $this->returnSelf()
            ],
            [

                "processLoginRequest" => [1, []]
            ]
        );
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

    public function test_validation_failure_on_login_will_terminate()
    {

        $this->expectException(ValidationFailure::class); // then

        $sutName = ModuleLoginHandler::class;

        $this->massProvide([

            $sutName => $this->negativeDouble($sutName, [

                "isValidRequest" => false // given
            ]),

            AuthContract::class => $this->positiveDouble(AuthContract::class, [

                "getLoginCollection" => $this->negativeDouble(LoginFlowMediator::class)
            ])
        ]);

        $this->entrance->handleLoginRequest(); // when
    }
}
