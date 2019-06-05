<?php
/**
 * Created by Malik Abiola.
 * Date: 05/02/2016
 * Time: 22:55
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Models;

use MAbiola\Paystack\Abstractions\Model;
use MAbiola\Paystack\Contracts\ModelInterface;
use MAbiola\Paystack\Contracts\PlansInterface;
use MAbiola\Paystack\Exceptions\PaystackUnsupportedOperationException;
use MAbiola\Paystack\Repositories\PlanResource;

class Plan extends Model implements PlansInterface, ModelInterface
{
    private $planResource;

    protected $plan_code;

    protected $name;

    protected $description;

    protected $amount;

    protected $interval;

    protected $send_invoices = true;

    protected $send_sms = true;

    protected $hosted_page = false;

    protected $hosted_page_url;

    protected $hosted_page_summary;

    protected $currency;

    protected $subscriptions = [];

    /**
     * Plan constructor.
     *
     * @param PlanResource $planResource
     */
    public function __construct(PlanResource $planResource)
    {
        $this->planResource = $planResource;
    }

    /**
     * Get plan by plan code.
     *
     * @param $planCode
     *
     * @throws
     * @throws \Exception
     *
     * @return $this
     */
    public function getPlan($planCode)
    {
        $plan = $this->planResource->get($planCode);
        if ($plan instanceof \Exception) {
            throw $plan;
        }

        $this->setDeletable(true);

        return $this->_setAttributes($plan);
    }

    /**
     * Create new Plan Object.
     *
     * @param $name
     * @param $description
     * @param $amount
     * @param $currency
     * @param array $otherAttributes
     *
     * @return $this
     */
    public function make($name, $description, $amount, $currency, array $otherAttributes = [])
    {
        $this->name = $name;
        $this->description = $description;
        $this->amount = $amount;
        $this->currency = $currency;

        $this->_setAttributes($otherAttributes);
        $this->setCreatable(true);

        return $this;
    }

    /**
     * set attributes to update.
     *
     * @param array $updateData
     *
     * @return $this
     */
    public function setUpdateData(array $updateData)
    {
        $this->_setAttributes($updateData);
        $this->setUpdateable(true);

        return $this;
    }

    /**
     * Save/Update plan object.
     *
     * @throws \Exception
     * @throws \Exception|mixed
     * @throws null
     *
     * @return $this|Plan
     */
    public function save()
    {
        $resourceResponse = null;

        if ($this->isCreatable() && !$this->isUpdateable()) { //available for creation
            $resourceResponse = $this->planResource->save($this->transform(ModelInterface::TRANSFORM_TO_JSON_ARRAY));
        }

        if ($this->isUpdateable() && !$this->isCreatable()) { //available for update
            $resourceResponse = $this->planResource->update(
                $this->plan_code,
                $this->transform(ModelInterface::TRANSFORM_TO_JSON_ARRAY)
            );
        }

        if ($resourceResponse == null) {
            throw new \InvalidArgumentException('You Cant Perform This Operation on an empty plan');
        }

        if ($resourceResponse instanceof \Exception) {
            throw $resourceResponse;
        }

        return $this->_setAttributes($resourceResponse);
    }

    /**
     * delete Plan.
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function delete()
    {
        //        if ($this->isDeletable()) {
//            $resourceResponse = $this->planResource->delete($this->plan_code);
//            if ($resourceResponse instanceof \Exception) {
//                throw $resourceResponse;
//            }
//
//            return !!$resourceResponse['status'];
//        }

        throw new PaystackUnsupportedOperationException("Plan can't be deleted", 405);
    }

    /**
     * Outward presentation of object.
     *
     * @param $transformMode
     *
     * @return mixed
     */
    public function transform($transformMode = '')
    {
        $planObject = [
            'plan_code'           => $this->plan_code,
            'name'                => $this->name,
            'description'         => $this->description,
            'amount'              => $this->amount,
            'interval'            => $this->interval,
            'currency'            => $this->currency,
            'hosted_page'         => $this->hosted_page,
            'hosted_page_url'     => $this->hosted_page_url,
            'hosted_page_summary' => $this->hosted_page_summary,
            'subscription_count'  => count($this->subscriptions),
        ];
        switch ($transformMode) {
            case ModelInterface::TRANSFORM_TO_JSON_ARRAY:
                return json_encode($planObject);
            default:
                return $planObject;
        }
    }
}
