<?php

namespace Suphle\Adapters\Presentation\Hotwire\Formats;

use Suphle\Response\Format\Redirect;

use Suphle\Hydration\Structures\CallbackDetails;

use Closure;

class RedirectHotwireStream extends BaseHotwireStream
{

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
}
