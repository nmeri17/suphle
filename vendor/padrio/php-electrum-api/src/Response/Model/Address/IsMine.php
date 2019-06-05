<?php

namespace Electrum\Response\Model\Address;

use Electrum\Response\ResponseInterface;

/**
 * @author Pascal Krason <p.krason@check24.de>
 */
class IsMine implements ResponseInterface
{

    /**
     * @var bool
     */
    private $isMine = false;

    /**
     * @return bool
     */
    public function isMine()
    {
        return $this->isMine;
    }

    /**
     * @param bool $isMine
     *
     * @return IsMine
     */
    public function setIsMine($isMine)
    {
        $this->isMine = $isMine;

        return $this;
    }

}