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

use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\HotwireCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\CustomInterfaceCollection;

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

    protected Employment $employment1;
    protected Employment $employment2;

    protected function setUp(): void // $this->markTestSkipped();
    {$this->databaseAllSetup();

        $this->csrfField = [

            CsrfGenerator::TOKEN_FIELD => $this->getContainer()

            ->getClass(CsrfGenerator::class)->newToken()
        ];

        [$this->employment1, $this->employment2] = $this->replicator->getRandomEntities(2);
    }

    protected function getModules(): array
    {

        $interfaceCollection = new class () extends CustomInterfaceCollection {
            public function simpleBinds(): array
            {

                return array_merge(parent::simpleBinds(), [

                    RendererManager::class => HotwireRendererManager::class
                ]);
            }
        };

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "browserEntryRoute" => HotwireCollection::class
                ]);
            }, false, [

                "interfaceCollection" => $interfaceCollection::class
            ])
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

        return $this->$httpMethod($url, array_merge($this->csrfField, [

            "id" => $this->employment1->id,

            // given // missing id2 field
        ]), [

            PayloadStorage::ACCEPTS_KEY => $agentHeader
        ]) // when
        // then
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

                "id" => $this->employment1->id,

                // given // missing id2 field
            ]), [

                PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
            ]); // when
            // then
            $response->assertUnprocessable() // sanity check

            ->assertHeader(
                PayloadStorage::CONTENT_TYPE_KEY,
                BaseHotwireStream::TURBO_INDICATOR
            );

            $outputAsserter($response);
        });
    }

    public function hotwireFailureContent(): array
    {

        return [ // these frames/forms are expected to check the presence of validation errors and render them appropriately
            [
                self::DUAL_REDIRECT, self::POST_METHOD,

                function (TestResponseBridge $response) {

                    $this->assertStreamNode(BaseHotwireStream::REPLACE_ACTION, "update-form"); // we set the route collection target generator to fallback to this in event of validation failure

                    $this->assertEmploymentFormError(
                        $response,
                        BaseHotwireStream::REPLACE_ACTION,
                        BaseHotwireStream::BEFORE_ACTION,
                        $this->employment1 // these IDs should be considered implementation detail and don't really matter. They're determined by what's bound to the action's validator
                    );
                }
            ], [
                self::DUAL_RELOAD, self::PUT_METHOD,

                function (TestResponseBridge $response) {

                    $this->assertStreamNode(BaseHotwireStream::UPDATE_ACTION, "update-form");

                    $this->assertEmploymentFormError(
                        $response,
                        BaseHotwireStream::UPDATE_ACTION,
                        BaseHotwireStream::AFTER_ACTION,
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

        ->assertSee(
            "<input type=\"text\" name=\"id\" value=\"{$employment->id}\">",
            false
        )
        ->assertSee(
            "<input type=\"text\" name=\"id2\" value=\"\">",
            false
        )
        ->assertDontSee(
            "
				<div id=\"from-handler\">{$employment->id}</div>",
            false
        );

        if (!is_null($oppositeAction)) {

            $response->assertDontSee(ucfirst($oppositeAction) . " form");
        }
    }

    public function hotwireFailureToParse(): array
    {

        return [

            [
                self::DUAL_REDIRECT, self::POST_METHOD,

                "hotwire/replace-fragment"
            ], [

                self::DUAL_RELOAD, self::PUT_METHOD,

                "hotwire/update-fragment"
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

                    "parseRenderer" => [1, [ // then
                        $this->callback(function ($subject) use ($markupName) {

                            return str_contains($subject->getMarkupName(), $markupName);
                        })
                    ]]
                ])
            ]);

            $this->$httpMethod($url, array_merge($this->csrfField, [

                "id" => $this->employment1->id,

                // given // missing id2 field
            ]), [

                PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
            ]); // when
        });
    }

    public function hotwireSuccessContent(): array
    {

        $employment1Id = $this->employment1->id;

        $employment2Id = $this->employment2->id;

        return [

            [
                self::DUAL_REDIRECT, self::POST_METHOD,

                function (TestResponseBridge $response) use ($employment1Id, $employment2Id) {

                    $this->assertStreamNode(BaseHotwireStream::REPLACE_ACTION, "employment_". $employment1Id);

                    $this->assertStreamNode(BaseHotwireStream::BEFORE_ACTION, "employment_". $employment2Id);

                    $this->assertEmploymentSuccessContent(
                        $response,
                        BaseHotwireStream::REPLACE_ACTION,
                        $this->employment2 // neither id or 2 should be present but we're testing with id2 since that's the name hard-coded in the asserter
                    )->assertEmploymentSuccessContent(
                        $response,
                        BaseHotwireStream::BEFORE_ACTION,
                        $this->employment2
                    );
                }
            ], [
                self::DUAL_RELOAD, self::PUT_METHOD,

                function (TestResponseBridge $response) use ($employment1Id, $employment2Id) {

                    $this->assertStreamNode(BaseHotwireStream::AFTER_ACTION, "employment_". $employment1Id);

                    $this->assertStreamNode(BaseHotwireStream::UPDATE_ACTION, "employment_". $employment2Id);

                    $this->assertEmploymentSuccessContent(
                        $response,
                        BaseHotwireStream::AFTER_ACTION,
                        $this->employment2
                    )->assertEmploymentSuccessContent(
                        $response,
                        BaseHotwireStream::UPDATE_ACTION,
                        $this->employment2
                    );
                }
            ]
        ];
    }

    protected function assertEmploymentSuccessContent(TestResponseBridge $response, string $hotwireAction, Employment $employment): self
    {

        $response->assertSee(ucfirst($hotwireAction) . " form")

        ->assertDontSee("Validation errors")

        ->assertDontSee(
            "<input type=\"text\" name=\"id2\" value=\"{$employment->id}\">",
            false
        ); // since it's not expected to have gone through validation handler

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

                "id2" => $this->employment2->id
            ]), [

                PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
            ]); // when

            // then
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

                "id2" => $this->employment2->id
            ])) // when
            // then
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

    /**
     * Low-level to confirm actual behavior instead of relying on the user-facing asserter
    */
    public function test_dual_renderer_correctly_wraps_content()
    {

        $this->get(self::INITIAL_URL);

        $responseAsserter = $this->post(self::DUAL_REDIRECT, array_merge($this->csrfField, [

            "id" => $this->employment1->id,

            "id2" => $this->employment2->id
        ]), [

            PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
        ]); // when

        $frameTarget = (new ModelDetail())->idFromModel($this->employment2); // we're testing against the "before" coordinator, which makes use of id2

        $hotwireAction = BaseHotwireStream::BEFORE_ACTION;

        $upperCaseAction = ucfirst($hotwireAction). " form";

        // then
        $responseAsserter->assertSee($upperCaseAction)

        ->assertSee($this->employment2->id)

        ->assertSee($this->employment2->title)

        ->assertSee(
            "<turbo-stream action='$hotwireAction' targets='$frameTarget'>",
            false
        );

        $this->assertHotwireRedirect($responseAsserter);
    }

    public function test_absence_of_create_node_falls_back_to_available_on_failure()
    {

        $employment1Id = $this->employment1->id;

        $this->get(self::INITIAL_URL);

        $response = $this->post("/no-replace-node", array_merge($this->csrfField, [

            "id" => $employment1Id,

            // given // missing id2 field
        ]), [

            PayloadStorage::ACCEPTS_KEY => BaseHotwireStream::TURBO_INDICATOR
        ]) // when
        // then
        ->assertUnprocessable() // sanity check

        ->assertHeader(PayloadStorage::CONTENT_TYPE_KEY, BaseHotwireStream::TURBO_INDICATOR);

        $this->assertStreamNode(BaseHotwireStream::BEFORE_ACTION, "update-form");

        $this->assertStreamNode(BaseHotwireStream::APPEND_ACTION, "update-form");

        // can't use assertEmploymentFormError since none of the forms are bound to the renderer
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
            ]) // when
            // then
            ->assertHeader(
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
                        "employment_". $this->employment1->id
                    );
                }], [
                "/combine-delete", function (TestResponseBridge $response) {

                    $employment1Id = $this->employment1->id;

                    $this->assertStreamNode(
                        BaseHotwireStream::REMOVE_ACTION,
                        "employment_". $employment1Id
                    );
                    $this->assertStreamNode(
                        BaseHotwireStream::AFTER_ACTION,
                        "employment_". $employment1Id
                    );

                    $this->assertEmploymentSuccessContent(
                        $response,
                        BaseHotwireStream::AFTER_ACTION,
                        $this->employment1
                    );
                }]
        ];
    }
}
