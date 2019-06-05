<?php

namespace Electrum\Response\Model\Payment;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
class Amount
{
    /**
     * @var float
     */
    private $bitcoins = 0;

    /**
     * @var float
     */
    private $litecoins = 0;

    /**
     * @var int
     */
    private $satoshis = 0;

    /**
     * @return float
     */
    public function getBitcoins()
    {
        return $this->bitcoins;
    }

    /**
     * @param float $bitcoins
     *
     * @return Amount
     */
    public function setBitcoins($bitcoins)
    {
        $this->bitcoins = $bitcoins;

        return $this;
    }

    /**
     * @return float
     */
    public function getLitecoins()
    {
        return $this->litecoins;
    }

    /**
     * @param float $litecoins
     *
     * @return Amount
     */
    public function setLitecoins($litecoins)
    {
        $this->litecoins = $litecoins;

        return $this;
    }

    /**
     * @return int
     */
    public function getSatoshis()
    {
        return $this->satoshis;
    }

    /**
     * @param int $satoshis
     *
     * @return Amount
     */
    public function setSatoshis($satoshis)
    {
        $this->satoshis = $satoshis;

        return $this;
    }

}