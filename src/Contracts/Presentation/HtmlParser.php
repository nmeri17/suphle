<?php

namespace Suphle\Contracts\Presentation;

use Suphle\Contracts\Routing\OutputsCrudFiles;

interface HtmlParser extends OutputsCrudFiles
{
    public function parseRenderer(RendersMarkup $renderer): string;

    public function findInPath(string $markupPath): void;
}
