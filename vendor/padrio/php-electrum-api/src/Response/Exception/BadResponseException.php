<?php

namespace Electrum\Response\Exception;

use Electrum\Request\Exception\BadRequestException;
use Exception;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
class BadResponseException extends Exception
{
    /**
     * Extract electrum error from response
     *
     * @param array $response
     *
     * @return BadRequestException
     */
    public static function createFromElectrumResponse(array $response)
    {
        $message = '';
        $code = 0;

        if (isset($response['error']['message'])) {
            $message = vsprintf(
                'Electrum API returned error: `%s`',
                $response['error']['message']
            );
        }

        if (isset($response['error']['code'])) {
            $code = $response['error']['code'];
        }

        return new self($message, $code);
    }
}