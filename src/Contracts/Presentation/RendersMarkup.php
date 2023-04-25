<?php

namespace Suphle\Contracts\Presentation;

interface RendersMarkup
{
    public function getMarkupName(): string;

    /**
     * For exception diffusers to replace
    */
    public function setMarkupName(string $markupName): void;
}
