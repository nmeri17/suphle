<?php
/**
 * Created by Malik Abiola.
 * Date: 06/02/2016
 * Time: 15:50
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Repositories;

use GuzzleHttp\Client;
use MAbiola\Paystack\Abstractions\Resource;
use MAbiola\Paystack\Contracts\ResourceInterface;

class PlanResource extends Resource implements ResourceInterface
{
    private $paystackHttpClient;

    /**
     * PlanResource constructor.
     *
     * @param Client $paystackHttpClient
     */
    public function __construct(Client $paystackHttpClient)
    {
        $this->paystackHttpClient = $paystackHttpClient;
    }

    /**
     * Get Plan by id/code.
     *
     * @param $id
     *
     * @return \Exception|mixed
     */
    public function get($id)
    {
        $request = $this->paystackHttpClient->get(
            $this->transformUrl(Resource::PLANS_URL, $id)
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * get all plans. per page.
     *
     * @param null $page
     *
     * @return \Exception|mixed
     */
    public function getAll($page = null)
    {
        $page = !empty($page) ? "page={$page}" : '';
        $request = $this->paystackHttpClient->get(
            $this->transformUrl(Resource::PLANS_URL, '').$page
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * save new plan.
     *
     * @param $body
     *
     * @return \Exception|mixed
     */
    public function save($body)
    {
        $request = $this->paystackHttpClient->post(
            $this->transformUrl(Resource::PLANS_URL, ''),
            [
                'body'  => is_array($body) ? $this->toJson($body) : $body,
            ]
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * update plan.
     *
     * @param $id
     * @param $body
     *
     * @return \Exception|mixed
     */
    public function update($id, $body)
    {
        $request = $this->paystackHttpClient->put(
            $this->transformUrl(Resource::PLANS_URL, $id),
            [
                'body'  => is_array($body) ? $this->toJson($body) : $body,
            ]
        );

        return $this->processResourceRequestResponse($request);
    }

    /**
     * delete plan.
     *
     * @param $id
     *
     * @return \Exception|mixed
     */
    public function delete($id)
    {
        $request = $this->paystackHttpClient->delete(
            $this->transformUrl(Resource::PLANS_URL, $id)
        );

        return $this->processResourceRequestResponse($request);
    }
}
