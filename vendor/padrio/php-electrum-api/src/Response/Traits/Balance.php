<?php

namespace Electrum\Response\Traits;

/**
 * Trait Balance
 * @package Electrum\Response\Traits
 */
trait Balance
{
    /**
     * @var float
     */
    protected $confirmed = 0;

    /**
     * @var float
     */
    protected $unconfirmed = 0;

    /**
     * @return float
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param float $confirmed
     *
     * @return Balance
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * @return float
     */
    public function getUnconfirmed()
    {
        return $this->unconfirmed;
    }

    /**
     * @param float $unconfirmed
     *
     * @return Balance
     */
    public function setUnconfirmed($unconfirmed)
    {
        $this->unconfirmed = $unconfirmed;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->confirmed + $this->unconfirmed;
    }
}
