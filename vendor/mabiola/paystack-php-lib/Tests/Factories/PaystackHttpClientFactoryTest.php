<?php

namespace MAbiola\Paystack\Tests;

use GuzzleHttp\Client;
use MAbiola\Paystack\Factories\PaystackHttpClientFactory;

/**
 * Description of PaystackHttpClientFactoryTest.
 *
 * @author Doctormaliko
 */
class PaystackHttpClientFactoryTest extends BaseTestCase
{
    //put your code here
    public function testPaystackHttpClientReturnsGuzzleClient()
    {
        $this->assertInstanceOf(
            Client::class,
            PaystackHttpClientFactory::make()
        );
    }
}
