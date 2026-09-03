<?php
namespace Suphle\Contracts\Config;

interface Events extends ConfigMarker {
    
    /**
     * relative path/folder name
     * */
    public function getListenersPath(): string;
}
