<?php
namespace Suphle\Contracts\Config;

interface Auth extends ConfigMarker
{
    public function getModelObservers ():array;

    /**
     * expects given url to actually exist in the app
    */
    public function markupRedirect ():string;
}