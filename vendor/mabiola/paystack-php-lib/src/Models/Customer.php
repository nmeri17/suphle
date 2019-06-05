<?php
/**
 * Created by Malik Abiola.
 * Date: 04/02/2016
 * Time: 22:29
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Models;

use MAbiola\Paystack\Abstractions\Model;
use MAbiola\Paystack\Contracts\ModelInterface;
use MAbiola\Paystack\Exceptions\PaystackUnsupportedOperationException;
use MAbiola\Paystack\Repositories\CustomerResource;

class Customer extends Model implements ModelInterface
{
    protected $first_name;
    protected $last_name;
    protected $email;
    protected $phone;
    protected $customerId;

    public function __construct(CustomerResource $customerResource)
    {
        $this->customerResource = $customerResource;
    }

    /**
     * Get customer by ID.
     *
     * @param $customerId
     *
     * @throws \Exception|mixed
     *
     * @return $this
     */
    public function getCustomer($customerId)
    {
        //retrieve customer, set customer attributes
        $customerModel = $this->customerResource->get($customerId);
        if ($customerModel instanceof \Exception) {
            throw $customerModel;
        }

        $this->_setAttributes($customerModel);
        $this->setDeletable(true);

        return $this;
    }

    /**
     * set up a new customer object.
     *
     * @param $first_name
     * @param $last_name
     * @param $email
     * @param $phone
     * @param array $otherAttributes
     *
     * @return $this
     */
    public function make($first_name, $last_name, $email, $phone, $otherAttributes = [])
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->phone = $phone;

        $this->_setAttributes($otherAttributes);
        //set creatable
        $this->setCreatable(true);

        return $this;
    }

    /**
     * set update data on customer model.
     *
     * @param $updateAttributes
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setUpdateData($updateAttributes)
    {
        if (empty($updateAttributes)) {
            throw new \InvalidArgumentException('Update Attributes Empty');
        }

        $this->_setAttributes($updateAttributes);
        $this->setUpdateable(true);

        return $this;
    }

    /**
     * save/update customer model on paystack.
     *
     * @throws \Exception
     * @throws \Exception|mixed
     * @throws null
     *
     * @return $this
     */
    public function save()
    {
        $resourceResponse = null;

        if ($this->isCreatable() && !$this->isUpdateable()) { //available for creation
            $resourceResponse = $this->customerResource->save(
                $this->transform(ModelInterface::TRANSFORM_TO_JSON_ARRAY)
            );
        }

        if ($this->isUpdateable() && !$this->isCreatable()) { //available for update
            $resourceResponse = $this->customerResource->update(
                $this->customerId,
                $this->transform(ModelInterface::TRANSFORM_TO_JSON_ARRAY)
            );
        }

        if ($resourceResponse == null) {
            throw new \InvalidArgumentException('You Cant Perform This Operation on an empty customer object');
        }

        if ($resourceResponse instanceof \Exception) {
            throw $resourceResponse;
        }

        return $this->_setAttributes($resourceResponse);
    }

    /**
     * delete customer.
     *
     * @throws \Exception
     * @throws \Exception|mixed
     *
     * @return $this
     */
    public function delete()
    {
        //        if ($this->isDeletable()) {
//            $resourceResponse = $this->customerResource->delete($this->customerId);
//            if ($resourceResponse instanceof \Exception) {
//                throw $resourceResponse;
//            }
//
//            return !!$resourceResponse['status'];
//        }

        throw new PaystackUnsupportedOperationException("Customer can't be deleted", 405);
    }
}
