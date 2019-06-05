<?php

namespace Electrum\Response\Hydrator\Payment;

use Zend\Hydrator\NamingStrategy\MapNamingStrategy;
use Zend\Hydrator\Reflection;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
class Amount extends Reflection
{
    /**
     * Initializes a new instance of this class.
     */
    public function __construct()
    {
        parent::__construct();

        $namingStrategy = new MapNamingStrategy([
            'amount (BTC)' => 'bitcoins',
            'amount (LTC)' => 'litecoins',
            'amount' => 'satoshis',
        ]);

        $this->setNamingStrategy($namingStrategy);
    }

}