<?php
/**
 * Created by Malik Abiola.
 * Date: 04/02/2016
 * Time: 22:17
 * IDE: PhpStorm
 * Helper functions.
 */
namespace MAbiola\Paystack\Helpers;

use Illuminate\Support\Str;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid;

trait Utils
{
    /**
     * Transform url by replacing dummy data.
     *
     * @param $url
     * @param $id
     * @param string $key
     *
     * @return mixed
     */
    public static function transformUrl($url, $id, $key = '')
    {
        return str_replace(!empty($key) ? $key : ':id', $id, $url);
    }

    /**
     * Create Json encoded representation of object.
     *
     * @param $object
     *
     * @return string
     */
    public function toJson($object)
    {
        return json_encode($object);
    }

    /**
     * generates a unique transaction ref used for init-ing transactions.
     *
     * @return mixed|null
     */
    public static function generateTransactionRef()
    {
        try {
            return str_replace('-', '', Uuid::uuid1()->toString());
        } catch (UnsatisfiedDependencyException $e) {
            return null;
        }
    }

    /**
     * Converts a bowl of object to an array.
     *
     * @todo: replace with function that only shows accessible properties of the object
     *
     * @param $object
     *
     * @return array
     */
    public function objectToArray($object)
    {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }
        if (is_object($object)) {
            $object = get_object_vars($object);
        }

        return array_map([get_class(), 'objectToArray'], $object);
    }

    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     * From Laravel/lumen-framework/src/helpers.php.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
