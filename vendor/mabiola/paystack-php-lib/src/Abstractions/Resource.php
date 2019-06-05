<?php
/**
 * Created by Malik Abiola.
 * Date: 06/02/2016
 * Time: 16:02
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Abstractions;

use Illuminate\Http\Response;
use MAbiola\Paystack\Exceptions\ExceptionHandler;
use MAbiola\Paystack\Helpers\Utils;
use Psr\Http\Message\ResponseInterface;

abstract class Resource
{
    use Utils;

    const INITIALIZE_TRANSACTION = '/transaction/initialize';
    const VERIFY_TRANSACTION = '/transaction/verify/:reference';
    const GET_TRANSACTION = '/transaction/:id';
    const GET_TRANSACTION_TOTALS = '/transaction/totals';
    const CHARGE_AUTHORIZATION = '/transaction/charge_authorization';
    const CHARGE_TOKEN = '/transaction/charge_token';
    const CUSTOMERS_URL = '/customer/:id';
    const PLANS_URL = '/plan/:id';

    /**
     * Checks request response and dispatch result to appropriate handler.
     *
     * @param ResponseInterface $request
     *
     * @return \Exception|mixed
     */
    public function processResourceRequestResponse(ResponseInterface $request)
    {
        $response = json_decode($request->getBody()->getContents());

        if (Response::HTTP_OK !== $request->getStatusCode() && Response::HTTP_CREATED !== $request->getStatusCode()) {
            return ExceptionHandler::handle(get_class($this), $response, $request->getStatusCode());
        }

        return (isset($response->data)) ? json_decode(json_encode($response->data), true) : json_decode(json_encode($response), true);
    }
}
