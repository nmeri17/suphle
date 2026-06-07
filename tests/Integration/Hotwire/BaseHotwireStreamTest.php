<?php

namespace Suphle\Tests\Integration\Hotwire;

use Suphle\Contracts\{Requests\CoodinatorManager, Config\Router, Response\RendererManager, Presentation\HtmlParser};
use Suphle\Adapters\Presentation\Hotwire\{HotwireRendererManager, HotwireAsserter, Formats\BaseHotwireStream};
use Suphle\Adapters\Presentation\Blade\DefaultBladeAdapter;
use Suphle\Response\Format\{Reload, Redirect};
use Suphle\Security\CSRF\CsrfGenerator;
use Suphle\Request\PayloadStorage;
use Suphle\Adapters\Orms\Eloquent\Models\ModelDetail;
use Suphle\Exception\{Diffusers\ValidationFailureDiffuser, Explosives\ValidationFailure};
use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer, Proxies\Extensions\TestResponseBridge, Condiments\BaseDatabasePopulator};
use Suphle\Tests\Integration\Services\CoodinatorManager\HttpValidationTest;
use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\HotwireCollection, Meta\ModuleOneDescriptor, Config\RouterMock, Coordinators\HotwireCoordinator, Meta\CustomInterfaceCollection};
use Suphle\Tests\Mocks\Models\Eloquent\Employment;

/**
 * @\depends HttpValidationTest
 */
class BaseHotwireStreamTest extends ModuleLevelTest
{
    use BaseDatabasePopulator, HotwireAsserter {

        BaseDatabasePopulator::setUp as databaseAllSetup;
    }

    //protected bool $debugCaughtExceptions = true;

    protected const INITIAL_URL = "/init-post",
    DUAL_REDIRECT = "/hotwire-redirect",
    DUAL_RELOAD = "/hotwire-reload",
    POST_METHOD = "post", PUT_METHOD = "put";

    protected array $csrfField;

    protected Employment $employment1, $employment2;

    protected function setUp(): void
    {
        $this->databaseAllSetup();

        $this->csrfField = [
            CsrfGenerator::TOKEN_FIELD => $this->getContainer()
                ->getClass(CsrfGenerator::class)->newToken()
        ];

        [$this->employment1, $this->employment2] = $this->replicator->getRandomEntities(2);
    }

    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(Router::class, RouterMock::class, [
                    "getCoordinatorClassesToScan" => [
                        HotwireCoordinator::class
                    ]
                ])
                ->replaceWithMock(RendererManager::class, HotwireRendererManager::class);
            })
        ];
    }

    protected function getActiveEntity(): string
    {
        return Employment::class;
    }

    /**
     * @dataProvider userAgentHeaders
     */
    public function test_regular_renderer_failure_yields_non_hotwire_response(?string $agentHeader)
    {
        $this->sendFailRedirect(
            "/regular-markup",
            self::POST_METHOD,
            $agentHeader
        );
    }

    public function userAgentHeaders(): array
    {
        return [
            [null], [BaseHotwireStream::TURBO_INDICATOR]
        ];
    }

    protected function sendFailRedirect(string $url, string $httpMethod, ?string $agentHeader = null): TestResponseBridge
    {
        $this->get(self::INITIAL_URL);

        // Omit required field1/field2 payloads to explicitly force a validation drop
        return $this->$httpMethod($url, array_merge($this->csrfField, [
            "id" => $this->employment1->id,
        ]), [
            PayloadStorage::ACCEPTS_KEY => $agentHeader
        ])
        ->assertRedirect(self::INITIAL_URL);
    }

    public function urlsToHotwireRequests(): array
    {
        return [
            [
                self::DUAL_REDIRECT, self::POST_METHOD
            ], [
                self::DUAL_RELOAD, self::PUT_METHOD
            ]
        ];
    }

    /**
     * @dataProvider urlsToHotwireRequests
     */
    public function test_regular_request_to_dual_failure_reverts_to_previous(string $url, string $httpMethod)
    {
        $this->sendFailRedirect($url, $httpMethod, "");
    }

    public function test_hotwire_to_dual_failure_filters_current()
    {
        $this->dataProvider([
            $this->hotwireFailureContent(...)
        ], function (
            string $url,
            string $httpMethod,
            callable $outputAsserter
        ) {
            $this->get(self::INITIAL_URL);

            $response = $this->$httpMethod($url, array_merge($this->csrfField, [
                "id" => $this->employment1->id, // Triggers validation failure due to missing field1/field2
            ]), [
                PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
            ]);
            
            $response->assertUnprocessable()
            ->assertHeader(
                PayloadStorage::CONTENT_TYPE_KEY,
                BaseHotwireStream::TURBO_INDICATOR
            );

            $outputAsserter($response);
        });
    }

    public function hotwireFailureContent(): array
    {
        return [ 
            [
                self::DUAL_REDIRECT, self::POST_METHOD,
                function (TestResponseBridge $response) {
                    $this->assertStreamNode(BaseHotwireStream::REPLACE_ACTION, "#form-container"); 

                    $this->assertEmploymentFormError(
                        $response,
                        BaseHotwireStream::REPLACE_ACTION,
                        null,
                        $this->employment1 
                    );
                }
            ], [
                self::DUAL_RELOAD, self::PUT_METHOD,
                function (TestResponseBridge $response) {
                    $this->assertStreamNode(BaseHotwireStream::REPLACE_ACTION, "#form-container");

                    $this->assertEmploymentFormError(
                        $response,
                        BaseHotwireStream::REPLACE_ACTION,
                        null,
                        $this->employment1
                    );
                }
            ]
        ];
    }

    protected function assertEmploymentFormError(
        TestResponseBridge $response,
        string $hotwireAction,
        ?string $oppositeAction,
        Employment $employment
    ): void {
        $response->assertSee(ucfirst($hotwireAction) . " form")
        ->assertSee("Validation errors")
        ->assertSee("name=\"field1\"", false)
        ->assertSee("name=\"field2\"", false);

        if (!is_null($oppositeAction)) {
            $response->assertDontSee(ucfirst($oppositeAction) . " form");
        }
    }

    public function hotwireFailureToParse(): array
    {
        return [
            [
                self::DUAL_REDIRECT, self::POST_METHOD,
                "hotwire/form-fragment"
            ], [
                self::DUAL_RELOAD, self::PUT_METHOD,
                "hotwire/form-fragment"
            ]
        ];
    }

    public function test_hotwire_to_dual_failure_parses_only_one_partial()
    {
        $this->dataProvider([
            $this->hotwireFailureToParse(...)
        ], function (
            string $url,
            string $httpMethod,
            string $markupName
        ) {
            $this->massProvide([
                HtmlParser::class => $this->positiveDouble(DefaultBladeAdapter::class, [
                    "parseRenderer" => "page contents"
                ], [
                    "parseRenderer" => [1, [
                        $this->callback(function ($subject) use ($markupName) {
                            return str_contains($subject->getMarkupName(), $markupName);
                        })
                    ]]
                ])
            ]);

            $this->$httpMethod($url, array_merge($this->csrfField, [
                "id" => $this->employment1->id,
            ]), [
                PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
            ]);
        });
    }

    public function hotwireSuccessContent(): array
    {
        return [
            [
                self::DUAL_REDIRECT, self::POST_METHOD,
                function (TestResponseBridge $response) {
                    // 100 represents the fixed mock model identity returned via updateModels
                    $this->assertStreamNode(BaseHotwireStream::REPLACE_ACTION, "100");
                    
                    // 101 represents the secondary layout item id returned via fetchAlternateFragmentData
                    $this->assertStreamNode(BaseHotwireStream::BEFORE_ACTION, "101");

                    $this->assertEmploymentSuccessContent($response, BaseHotwireStream::REPLACE_ACTION);
                    $this->assertEmploymentSuccessContent($response, BaseHotwireStream::BEFORE_ACTION);
                }
            ], [
                self::DUAL_RELOAD, self::PUT_METHOD,
                function (TestResponseBridge $response) {
                    $this->assertStreamNode(BaseHotwireStream::AFTER_ACTION, "100");
                    $this->assertStreamNode(BaseHotwireStream::UPDATE_ACTION, "101");

                    $this->assertEmploymentSuccessContent($response, BaseHotwireStream::AFTER_ACTION);
                    $this->assertEmploymentSuccessContent($response, BaseHotwireStream::UPDATE_ACTION);
                }
            ]
        ];
    }

    protected function assertEmploymentSuccessContent(TestResponseBridge $response, string $hotwireAction): self
    {
        $response->assertSee(ucfirst($hotwireAction) . " form")
        ->assertDontSee("Validation errors");

        return $this;
    }

    public function test_hotwire_to_dual_success_yields_all()
    {
        $this->dataProvider([
            $this->hotwireSuccessContent(...)
        ], function (string $url, string $httpMethod, callable $outputAsserter) {
            $this->get(self::INITIAL_URL);

            $responseAsserter = $this->$httpMethod($url, array_merge($this->csrfField, [
                "id" => $this->employment1->id,
                "field1" => "valid string data",
                "field2" => 42
            ]), [
                PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
            ]);

            $responseAsserter->assertHeader(
                PayloadStorage::CONTENT_TYPE_KEY,
                BaseHotwireStream::TURBO_INDICATOR
            );

            $outputAsserter($responseAsserter);
        });
    }

    public function test_regular_request_to_dual_success_proceeds()
    {
        $this->dataProvider([
            $this->regularSuccessContent(...)
        ], function (string $url, string $httpMethod, int $statusCode, array $expectedHeaders) {
            $this->get(self::INITIAL_URL);

            $response = $this->$httpMethod($url, array_merge($this->csrfField, [
                "id" => $this->employment1->id,
                "field1" => "valid string data",
                "field2" => 42
            ]))
            ->assertStatus($statusCode);

            foreach ($expectedHeaders as $header => $value) {
                $response->assertHeader($header, $value);
            }
        });
    }

    public function regularSuccessContent(): array
    {
        return [
            [
                self::DUAL_REDIRECT, self::POST_METHOD,
                Redirect::STATUS_CODE,
                [
                    PayloadStorage::LOCATION_KEY => "/"
                ]
            ], [
                self::DUAL_RELOAD, self::PUT_METHOD,
                Reload::STATUS_CODE,
                [
                    PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::HTML_HEADER_VALUE
                ]
            ]
        ];
    }

    public function test_dual_renderer_correctly_wraps_content()
    {
        $this->get(self::INITIAL_URL);

        $responseAsserter = $this->post(self::DUAL_REDIRECT, array_merge($this->csrfField, [
            "id" => $this->employment1->id,
            "field1" => "valid string data",
            "field2" => 12
        ]), [
            PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
        ]);

        $frameTarget = "101"; // Target extracted from the secondary layout fetch component
        $hotwireAction = BaseHotwireStream::BEFORE_ACTION;

        $responseAsserter->assertSee(ucfirst($hotwireAction). " form")
        ->assertSee("fetched_variant")
        ->assertSee(
            "<turbo-stream action='$hotwireAction' targets='$frameTarget'>",
            false
        );

        $this->assertHotwireRedirect($responseAsserter);
    }

    public function test_delete_node_renders_correctly()
    {
        $this->dataProvider([
            $this->deleteNodeUrls(...)
        ], function (string $url, callable $outputAsserter) {
            $this->get(self::INITIAL_URL);

            $responseAsserter = $this->delete($url, array_merge($this->csrfField, [
                "id" => $this->employment1->id
            ]), [
                PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
            ]);

            $responseAsserter->assertHeader(
                PayloadStorage::CONTENT_TYPE_KEY,
                BaseHotwireStream::TURBO_INDICATOR
            );

            $this->assertHotwireRedirect($responseAsserter);
            $outputAsserter($responseAsserter);
        });
    }

    public function deleteNodeUrls(): array
    {
        return [
            [
                "/delete-single", function (TestResponseBridge $response) {
                    $this->assertStreamNode(
                        BaseHotwireStream::REMOVE_ACTION,
                        "employment_1" // Resolved directly from EmploymentId2Builder fallback value
                    );
                }
            ], [
                "/combine-delete", function (TestResponseBridge $response) {
                    $this->assertStreamNode(
                        BaseHotwireStream::REMOVE_ACTION,
                        "employment_1"
                    );
                    $this->assertStreamNode(
                        BaseHotwireStream::AFTER_ACTION,
                        "200" // Extracted dynamically from fetchAncillaryRecord model payload resolver
                    );
                }
            ]
        ];
    }

    public function test_hotwire_failure_honors_custom_turbo_target_payload()
    {
        $this->get(self::INITIAL_URL);

        $response = $this->post(self::DUAL_REDIRECT, array_merge($this->csrfField, [
            "id" => $this->employment1->id,
            "_turbo_target" => "#custom-login-zone" // Dynamic override input field
        ]), [
            PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
        ]);

        $response->assertUnprocessable();
        
        // Asserts that the response custom-targeted the container passed in payload storage
        $this->assertStreamNode(BaseHotwireStream::REPLACE_ACTION, "#custom-login-zone");
    }
}