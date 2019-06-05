<?php
/**
 * Created by Malik Abiola.
 * Date: 03/02/2016
 * Time: 03:07
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Contracts;

interface ModelInterface
{
    const TRANSFORM_TO_JSON_ARRAY = 'json';
    const TRANSFORM_TO_ARRAY = 'array';
    const TRANSFORM_TO_STRING = 'string';

    /**
     * Outward presentation of object.
     *
     * @param $transformMode
     *
     * @return mixed
     */
    public function transform($transformMode);

    /**
     * Set attributes of the model.
     *
     * @param $attributes
     *
     * @return mixed
     */
    public function _setAttributes($attributes);

    /**
     * Convert object to array.
     *
     * @return array
     */
    public function toArray();
}
