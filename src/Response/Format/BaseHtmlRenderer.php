<?php

namespace Suphle\Response\Format;

use Suphle\Contracts\Presentation\{HtmlParser};

use Suphle\Contracts\IO\Session;

use Suphle\Services\Decorators\VariableDependencies;

#[VariableDependencies(["setHtmlParser", "setSession" ])]
abstract class BaseHtmlRenderer extends GenericRenderer
{
    public const STATUS_CODE = 200;

    protected int $statusCode = self::STATUS_CODE;

    protected HtmlParser $htmlParser;

    protected Session $sessionClient;

    public function setHtmlParser(HtmlParser $parser): void
    {

        $this->htmlParser = $parser;
    }

    public function setSession(Session $sessionClient): void
    {

        $this->sessionClient = $sessionClient;
    }

    public function getHeaders(): array
    {
        $cookieContent = $this->sessionClient->getAsCookieString();

        if (!empty($cookieContent))

        	$this->setHeaders($this->statusCode, [

	            "Set-Cookie" => $cookieContent
	        ]);

        return $this->headers;
    }
}
