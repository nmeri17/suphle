<?php

namespace Suphle\Contracts\Presentation;

use Suphle\Contracts\Routing\Crud\OutputsCrudFiles;

use Suphle\Response\Format\BaseHtmlRenderer;

interface HtmlParser extends OutputsCrudFiles
{
    public function parseRenderer(BaseHtmlRenderer $renderer): string;

    public function findInPath(string $markupPath): void;
}
