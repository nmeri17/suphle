<?php
/**
 * Created by Malik Abiola.
 * Date: 16/02/2016
 * Time: 11:53
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Tests;

use MAbiola\Paystack\Models\Transaction;

class TransactionModelObjectTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testMakeReturnsTransactionObjectWithModelAttributes()
    {
        $transactionObject = Transaction::make($this->transactionDetailsResponseData);

        $this->assertInstanceOf(Transaction::class, $transactionObject);
        $this->assertEquals($this->transactionDetailsResponseData['reference'], $transactionObject->get('reference'));

        $this->assertTrue(is_array($transactionObject->_toArray()));
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
