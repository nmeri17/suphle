<?php

namespace Suphle\Tests\Integration\Services\CoodinatorManager;

use Suphle\Contracts\{Config\Router as RouterContract, Requests\ValidationEvaluator};

use Suphle\Config\Router;

use Suphle\Security\CSRF\CsrfGenerator;

use Suphle\Exception\{Diffusers\ValidationFailureDiffuser, Explosives\ValidationFailure};

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\ValidatorCoordinator};

class HttpValidationTest extends ModuleLevelTest
{
    protected const FAIL_RETRY_TIMES = 3;

    // protected bool $debugCaughtExceptions = true;

    protected string $coordinatorName = ValidatorCoordinator::class;

    protected array $csrfField;

    protected function setUp(): void
    {

        parent::setUp();

        $this->csrfField = [

            CsrfGenerator::TOKEN_FIELD => $this->getContainer()

            ->getClass(CsrfGenerator::class)->newToken()
        ];
    }

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "getCoordinatorClassesToScan" => [$this->coordinatorName]
                ]);
            })
        ];
    }

    public function test_failed_validation_calls_diffuser_builder()
    {

        $this->massProvide([

            ValidationEvaluator::class => $this->getValidationEvaluator()
        ]);

        $response = $this->post($this->expandUrl("postWithValidator"), $this->csrfField); // when
    }

    protected function getValidationEvaluator():ValidationEvaluator {

        return $this->positiveDouble(ValidationEvaluator::class, [], [

            "validationRenderer" => [1, [

                $this->callback(function ($subject) {

                    return empty(array_diff(array_keys($subject), [

                        ValidationFailureDiffuser::PAYLOAD_KEY,

                        ValidationFailureDiffuser::ERRORS_PRESENCE
                    ])); // contains all keys or array1 == array2
                })
            ]]
        ]);
    }

    public function test_failed_validation_always_adds_errors_to_json_renderer()
    {
        $handleGetUrl = $this->expandUrl("handleGet");

        $withValidatorUrl = $this->expandUrl("postWithValidator");

        for ($i = 0; $i < self::FAIL_RETRY_TIMES; $i++) {

            $this->get($handleGetUrl); // given

            $response = $this->post($withValidatorUrl, $this->csrfField); // when

            // then
            $response->assertUnprocessable()

            ->assertJsonValidationErrorFor(
                "foo",
                ValidationFailureDiffuser::ERRORS_PRESENCE
            )
            ->assertJsonMissing(["message" => "mercy"]);
        }
    }

    public function test_failed_validation_always_reverts_errors_to_previous_on_browser()
    {
        $handleGetUrl = $this->expandUrl("handleGet");

        $validatorRedirectUrl = $this->expandUrl("postWithValidatorRedirect");

        for ($i = 0; $i < self::FAIL_RETRY_TIMES; $i++) {

            $this->get($handleGetUrl); // given

            $response = $this->post($validatorRedirectUrl, $this->csrfField); // when

            // then
            $response->assertRedirect();
        }
    }
}
