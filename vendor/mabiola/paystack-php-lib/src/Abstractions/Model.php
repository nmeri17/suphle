<?php
/**
 * Created by Malik Abiola.
 * Date: 06/02/2016
 * Time: 18:04
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Abstractions;

use MAbiola\Paystack\Contracts\ModelInterface;
use MAbiola\Paystack\Helpers\Utils;

abstract class Model
{
    use Utils;
    /**
     * Determines if a model can be updated.
     *
     * @var bool
     */
    protected $updateable = false;
    /**
     * Determines if a model is creatable i.e. if model is instantiated/built, can it be saved.
     *
     * @var bool
     */
    protected $creatable = false;
    /**
     * Determines if a model object can be deleted by call the delete method on it.
     *
     * @var bool
     */
    protected $deletable = false;

    /**
     * Get specific model attribute(s)
     * Accepts array of attributes, comma separated attributes or individual attribute.
     *
     * @param  $attributes
     *
     * @return mixed
     */
    public function get($attributes)
    {
        //get function args
        $argsAsArray = func_get_args();
        //is attributes passed as args is an array and count greater than 1
        if (!is_array($attributes) && count($argsAsArray) > 1) {
            //recalls itself to get attributes as array
            return call_user_func([get_class(), 'get'], $argsAsArray);
        }
        //if just args is not an array or comma separated list
        if (!is_array($attributes) && count($argsAsArray) == 1) {
            return isset($this->{$attributes}) ? $this->{$attributes} : null;
        }

        $attributesGet = [];
        foreach ($attributes as $attribute) {
            $attributesGet[$attribute] = isset($this->{$attribute}) ? $this->{$attribute} : null;
        }

        return $attributesGet;
    }

    /**
     * Set attributes of the model.
     *
     * @param $attributes
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function _setAttributes($attributes)
    {
        if (is_array($attributes)) {
            foreach ($attributes as $attribute => $value) {
                $this->{$attribute} = $value;
            }

            return $this;
        }

        //@todo: put real exception here cos exception' gon be thrown either ways, so put one that makes sense
        //or something else that has more meaning
        throw new \InvalidArgumentException('Invalid argument Passed to set attributes on object');
    }

    /**
     * get Outward presentation of object.
     *
     * @param $transformMode
     *
     * @return mixed
     */
    public function transform($transformMode = '')
    {
        switch ($transformMode) {
            case ModelInterface::TRANSFORM_TO_JSON_ARRAY:
                return json_encode($this->objectToArray($this));
            default:
                return $this->objectToArray($this);
        }
    }

    /**
     * Explicitly convert model object to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->objectToArray($this);
    }

    /**
     * check if model is updatable.
     *
     * @return bool
     */
    public function isUpdateable()
    {
        return $this->updateable;
    }

    /**
     * set model updateable.
     *
     * @param bool $updateable
     */
    protected function setUpdateable($updateable)
    {
        $this->updateable = $updateable;
    }

    /**
     * check if model can be created.
     *
     * @return bool
     */
    public function isCreatable()
    {
        return $this->creatable;
    }

    /**
     * set model can be created.
     *
     * @param bool $creatable
     */
    protected function setCreatable($creatable)
    {
        $this->creatable = $creatable;
    }

    /**
     * check if model is deletable.
     *
     * @return bool
     */
    public function isDeletable()
    {
        return $this->deletable;
    }

    /**
     * set model deletable.
     *
     * @param bool $deletable
     */
    protected function setDeletable($deletable)
    {
        $this->deletable = $deletable;
    }
}
