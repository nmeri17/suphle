<?php

namespace MAbiola\Paystack\Tests;

use MAbiola\Paystack\Abstractions\Resource;
use MAbiola\Paystack\Helpers\Utils;

/**
 * Created by Malik Abiola.
 * Date: 15/02/2016
 * Time: 21:42
 * IDE: PhpStorm.
 */
class UtilsTest extends BaseTestCase
{
    public function testGenerateTransactionRefIsUnique()
    {
        $this->assertNotEquals(Utils::generateTransactionRef(), Utils::generateTransactionRef());
    }

    public function testGetEnvReturnsDefaultValueWhenKeyNotFound()
    {
        $this->assertEquals('key', Utils::env('NOT_FOUND_KEY', 'key'));
    }

    public function testTransformUrlReturnsTransformedUrl()
    {
        $this->assertEquals('/customer/1', Utils::transformUrl(Resource::CUSTOMERS_URL, 1));
        $this->assertEquals(
            '/transaction/verify/transaction_reference',
            Utils::transformUrl(
                Resource::VERIFY_TRANSACTION,
                'transaction_reference',
                ':reference'
            )
        );
    }
}
