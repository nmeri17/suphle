<?php

namespace Electrum\Response\Model\Address;

use Electrum\Response\ResponseInterface;
use Electrum\Response\Traits\Balance;
use Electrum\Response\Traits\Transactions;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
class History implements ResponseInterface
{
    use Balance;
    use Transactions;
}