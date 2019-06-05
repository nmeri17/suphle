<?php
/**
 * Created by Malik Abiola.
 * Date: 05/02/2016
 * Time: 00:13
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Repositories;

use GuzzleHttp\Client;
use MAbiola\Paystack\Abstractions\Resource;

class TransactionResource extends Resource
{
    private $paystackHttpClient;

    /**
     * TransactionResource constructor.
     *
     * @param Client $paystackHttpClient
     */
    public function __construct(Client $paystackHttpClient)
    {
        $this->paystackHttpClient = $paystackHttpClient;
    }

    /**
     * get transaction by id.
     *
     * @param $id
     *
     * @return \Exception|mixed
     */
    public function get($id)
    {
        $request = $this->paystackHttpClient->get(
            $this->transformUrl(Resource::GET_TRANSACTION, $id)
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * Get all transactions.
     *
     * @param string $page
     *
     * @return \Exception|mixed
     */
    public function getAll($page = '')
    {
        $page = !empty($page) ? "/page={$page}" : '';
        $request = $this->paystackHttpClient->get(
            $this->transformUrl(Resource::GET_TRANSACTION, '').$page
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * Get transactions totals.
     */
    public function getTransactionTotals()
    {
        $request = $this->paystackHttpClient->get(
            Resource::GET_TRANSACTION_TOTALS
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * Verify Transaction by transaction reference.
     *
     * @param $reference
     *
     * @return \Exception|mixed
     */
    public function verify($reference)
    {
        $request = $this->paystackHttpClient->get(
            $this->transformUrl(Resource::VERIFY_TRANSACTION, $reference, ':reference')
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * Initialize one time transaction.
     *
     * @param $body
     *
     * @return \Exception|mixed
     */
    public function initialize($body)
    {
        $request = $this->paystackHttpClient->post(
            Resource::INITIALIZE_TRANSACTION,
            [
                'body'  => is_array($body) ? $this->toJson($body) : $body,
            ]
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * charge returning transaction.
     *
     * @param $body
     *
     * @return \Exception|mixed
     */
    public function chargeAuthorization($body)
    {
        $request = $this->paystackHttpClient->post(
            Resource::CHARGE_AUTHORIZATION,
            [
                'body'  => is_array($body) ? $this->toJson($body) : $body,
            ]
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * Charge Token.
     *
     * @param $body
     *
     * @return \Exception|mixed
     */
    public function chargeToken($body)
    {
        $request = $this->paystackHttpClient->post(
            Resource::CHARGE_TOKEN,
            [
                'body'  => is_array($body) ? $this->toJson($body) : $body,
            ]
        );

        return $this->processResourceRequestResponse($request);
    }
}
