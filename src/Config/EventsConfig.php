<?php
namespace Suphle\Config;

use Suphle\Contracts\Config\Events as EventContract;

class EventsConfig implements EventContract {

    public function getListenersPath(): string {

        return "Listeners";
    }
}