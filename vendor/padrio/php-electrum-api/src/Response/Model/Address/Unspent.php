<?php

namespace Electrum\Response\Model\Address;

use Electrum\Response\ResponseInterface;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
class Unspent implements ResponseInterface
{

    /**
     * @var array
     */
    private $utx = [];

    /**
     * @return array
     */
    public function getUtx()
    {
        return $this->utx;
    }

    /**
     * @param array $utx
     *
     * @return Unspent
     */
    public function setUtx($utx)
    {
        $this->utx = $utx;

        return $this;
    }

}