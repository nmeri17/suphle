<?php

namespace Suphle\Response\Format;

use Suphle\Contracts\{Presentation\HtmlParser, IO\Session};

use Suphle\Routing\Structures\RouteInfo;

use Suphle\Services\Decorators\VariableDependencies;

#[VariableDependencies(["setHtmlParser", "setSession", "setRouteInfo" ])]
abstract class BaseHtmlRenderer extends GenericRenderer
{
    public const STATUS_CODE = 200;

    protected int $statusCode = self::STATUS_CODE;

    protected HtmlParser $htmlParser;

    protected Session $sessionClient;

    protected RouteInfo $routeInfo;

    public function setHtmlParser(HtmlParser $parser): void {

        $this->htmlParser = $parser;
    }

    public function setSession(Session $sessionClient): void
    {

        $this->sessionClient = $sessionClient;
    }

    public function setRouteInfo(RouteInfo $routeInfo): void {

        $this->routeInfo = $routeInfo;
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
