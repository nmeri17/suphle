<?php

/**
 * Created by Malik Abiola.
 * Date: 01/02/2016
 * Time: 23:59
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Exceptions;

use Illuminate\Http\Response;

class ExceptionHandler
{
    /**
     * Handles errors encountered and returns the kind of exception they are.
     *
     * @param $resourceName
     * @param $response
     * @param $statusCode
     *
     * @return \Exception|PaystackNotFoundException|PaystackUnauthorizedException|PaystackValidationException
     */
    public static function handle($resourceName, $response, $statusCode)
    {
        switch ($statusCode) {
            case Response::HTTP_UNAUTHORIZED:
                return new PaystackUnauthorizedException($response, $statusCode);
            case Response::HTTP_NOT_FOUND:
                return new PaystackNotFoundException($response, $statusCode);
            case Response::HTTP_BAD_REQUEST:
                return new PaystackValidationException($response, $statusCode);
            case Response::HTTP_GATEWAY_TIMEOUT:
                return new PaystackInternalServerError($response, $statusCode);
            case Response::HTTP_INTERNAL_SERVER_ERROR:
//                @todo: when this happens, send email with details to paystack.
                return new PaystackInternalServerError('Internal Server Error.', $statusCode);
            default:
                return new \Exception('Unknown Error Occurred.', $statusCode);
        }
    }
}
