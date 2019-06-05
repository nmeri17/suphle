<?php

namespace Electrum\Response\Model\Wallet;

use Electrum\Response\ResponseInterface;
use Electrum\Response\Traits\Balance as BalanceTrait;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
class Balance implements ResponseInterface
{
    use BalanceTrait;
}