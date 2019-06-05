<?php

namespace Electrum\Response\Model\Wallet;

use Electrum\Response\ResponseInterface;

/**
 * @author zorn-v
 */
class Transaction implements ResponseInterface
{
    /**
     * @var array
     */
    private $input_addresses;

    /**
     * @var array
     */
    private $output_addresses;

    /**
     * @var string
     */
    private $date;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $txid;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $confirmations;


    /**
     * Get the value of Input Addresses
     *
     * @return array
     */
    public function getInputAddresses()
    {
        return $this->input_addresses;
    }

    /**
     * Get the value of Output Addresses
     *
     * @return array
     */
    public function getOutputAddresses()
    {
        return $this->output_addresses;
    }

    /**
     * Get the value of Date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get the value of Timestamp
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Get the value of Value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the value of Tx Id
     *
     * @return string
     */
    public function getTxId()
    {
        return $this->txid;
    }

    /**
     * Get the value of Label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the value of Confirmations
     *
     * @return int
     */
    public function getConfirmations()
    {
        return $this->confirmations;
    }

}
