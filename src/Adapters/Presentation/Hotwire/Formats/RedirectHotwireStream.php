<?php

namespace Suphle\Adapters\Presentation\Hotwire\Formats;

use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;

use Suphle\Response\Format\Redirect;

use Suphle\Hydration\Structures\CallbackDetails;

use Closure;

class RedirectHotwireStream extends BaseHotwireStream implements OpenApiRenderer
{
    use OpenApiRendererTrait;

    public const STATUS_CODE = 303;

    /**
     * @see https://turbo.hotwired.dev/handbook/drive#redirecting-after-a-form-submission
    */
    protected int $statusCode = self::STATUS_CODE;

    protected Closure $destination;

    public function __construct(Closure $destination)
    {
        $this->destination = $destination;
        $this->fallbackRenderer = new Redirect($destination);
    }

    public function setCallbackDetails(CallbackDetails $callbackDetails): void
    {
        $this->callbackDetails = $callbackDetails;
        $this->fallbackRenderer->setCallbackDetails($callbackDetails);
    }

    /**
     * Override default status code for RedirectHotwireStream
     */
    public static function getStatusCode(): int
    {
        return self::STATUS_CODE;
    }

    /**
     * Override default response schema for RedirectHotwireStream
     */
    public static function getResponseSchema(): array
    {
        return [
            'type' => 'string',
            'format' => 'html',
            'description' => static::getDescription(),
            'headers' => [
                'Location' => [
                    'description' => 'Redirect destination URL',
                    'schema' => [
                        'type' => 'string',
                        'format' => 'uri'
                    ]
                ]
            ]
        ];
    }

    /**
     * Override default description for RedirectHotwireStream
     */
    public static function getDescription(): string
    {
        return 'Turbo Stream redirect response';
    }
}
