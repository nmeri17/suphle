<?php

namespace Suphle\Tests\Integration\Auth;

use Suphle\Hydration\Container;

use Suphle\Contracts\Auth\{ModuleLoginHandler, LoginFlowMediator};

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Auth\{LoginRequestHandler, Renderers\BrowserLoginMediator};

use Suphle\Exception\Explosives\ValidationFailure;

use Suphle\Testing\{Condiments\DirectHttpTest, TestTypes\ModuleLevelTest};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

class LoginRequestHandlerTest extends ModuleLevelTest
{
    use DirectHttpTest;

    final public const LOGIN_PATH = "/login";

    private string $email = "foo@nmeri.com";

    public function getModules(): array
    {

        return [

            new ModuleOneDescriptor(new Container())
        ];
    }

    public function test_invalid_payload_terminates_request()
    {

        $this->expectException(ValidationFailure::class); // then

        $this->setJsonParams(self::LOGIN_PATH, [ // not necessary to set request method since we call the method directly, skipping the check; but using it all the same to avoid ambiguity on test's veracity

            "email" => $this->email
        ], "post"); // given

        $this->entrance->handleLoginRequest(); // when
    }

    public function test_valid_payload_tries_getting_response()
    {

        $this->setJsonParams(self::LOGIN_PATH, [

            "email" => $this->email,

            "password" => "alphon123"
        ], "post"); // given

        $this->massProvide([

            ModuleLoginHandler::class => $this->buildLoginHandler() // then
        ]);

        $this->entrance->handleLoginRequest(); // when
    }

    private function buildLoginHandler(): ModuleLoginHandler
    {

        $concreteName = LoginRequestHandler::class;

        $container = $this->getContainer();

        $renderer = $container->getClass(BrowserLoginMediator::class);

        $arguments = $container->whenType($concreteName)

        ->needsArguments([

            LoginFlowMediator::class => $renderer
        ])->getMethodParameters(
            Container::CLASS_CONSTRUCTOR,
            $concreteName
        );

        return $this->replaceConstructorArguments($concreteName, $arguments, [

            "setResponseRenderer" => $this->returnSelf(),

            "handlingRenderer" => $this->negativeDouble(BaseRenderer::class)
        ], [

            "processLoginRequest" => [1, []]
        ]);
    }
}
