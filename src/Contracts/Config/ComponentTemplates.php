<?php

namespace Suphle\Contracts\Config;

interface ComponentTemplates extends ConfigMarker
{
    public function getTemplateEntries(): array;
}
