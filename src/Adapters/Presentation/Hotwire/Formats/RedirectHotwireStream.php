<?php
namespace Suphle\Adapters\Presentation\Hotwire\Formats;

use Suphle\Response\Format\Redirect;

use Suphle\Hydration\Structures\CallbackDetails;

use Suphle\Request\RequestDetails;

use Suphle\Contracts\IO\Session;

use Suphle\Services\Decorators\VariableDependencies;

use Closure;


#[VariableDependencies(["setCallbackDetails" ])]
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
        $this->fallbackRenderer->setCallbackDetails($callbackDetails);
    }

    public function setRequestDetails(RequestDetails $requestDetails): void {

        $this->requestDetails = $requestDetails;
        $this->fallbackRenderer->setRequestDetails($requestDetails);
    }

    public function setSession(Session $sessionClient): void {

        $this->sessionClient = $sessionClient;
        $this->fallbackRenderer->setSession($sessionClient);
    }
}
