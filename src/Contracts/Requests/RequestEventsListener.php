<?php

namespace Suphle\Contracts\Requests;

use Suphle\Request\PayloadStorage;

interface RequestEventsListener
{
    public function handleRefreshEvent(PayloadStorage $payloadStorage): void;
}
