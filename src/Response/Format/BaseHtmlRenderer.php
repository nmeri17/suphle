<?php

namespace Suphle\Response\Format;

use Suphle\Contracts\Presentation\{RendersMarkup, HtmlParser};

use Suphle\Contracts\IO\Session;

use Suphle\Services\Decorators\VariableDependencies;

#[VariableDependencies(["setHtmlParser", "setSession" ])]
abstract class BaseHtmlRenderer extends GenericRenderer implements RendersMarkup
{
    protected string $markupName;

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

    /**
     * {@inheritdoc}
    */
    public function setMarkupName(string $markupName): void
    {

        $this->markupName = $markupName;
    }

    public function getMarkupName(): string
    {

        return $this->markupName;
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
