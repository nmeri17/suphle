<?php
/**
 * Created by Malik Abiola.
 * Date: 08/02/2016
 * Time: 22:37
 * IDE: PhpStorm
 * Create Guzzle HTTP Client that handles making requests and all.
 */
namespace MAbiola\Paystack\Factories;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use MAbiola\Paystack\Helpers\Utils;

class PaystackHttpClientFactory
{
    use Utils;

    public static function make($config = [])
    {
        //determine which mode/key to use
        $authorization = self::env('PAYSTACK_MODE') == 'test' ?
            self::env('PAYSTACK_TEST_SECRET_KEY') :
            self::env('PAYSTACK_LIVE_SECRET_KEY');

        $defaults = [
            'base_uri'      => 'https://api.paystack.co',
            'headers'       => [
                'Authorization' => 'Bearer '.$authorization,
                'Content-Type'  => 'application/json',
            ],
            'http_errors'   => false,
            'verify'        => self::env('PAYSTACK_MODE') == 'test' ? false : true, //add so local developments can work
            'handler'       => HandlerStack::create(new CurlHandler()), //use native curl
        ];

        if (!empty($config)) {
            $defaults = array_merge($defaults, $config);
        }

        return new Client($defaults);
    }
}
