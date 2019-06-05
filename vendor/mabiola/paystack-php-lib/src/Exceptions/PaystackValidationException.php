<?php
/**
 * Created by Malik Abiola.
 * Date: 08/02/2016
 * Time: 22:37
 * IDE: PhpStorm.
 */
namespace MAbiola\Paystack\Exceptions;

class PaystackValidationException extends BaseException
{
    private $response;

    public function __construct($response, $code)
    {
        $this->response = $response;
        parent::__construct($response->message, $code);
    }

    /**
     * Get validation errors that occurred in requests.
     *
     * @return array
     */
    public function getValidationErrors()
    {
        $errors = [];
        if (isset($this->response->errors)) {
            foreach ($this->response->errors as $error => $reasons) {
                $errors[] = [
                    'attribute' => $error,
                    'reason'    => $this->getValidationReasonsAsString($reasons),
                ];
            }
        }

        return $errors;
    }

    private function getValidationReasonsAsString($reasons)
    {
        $concatenatedReasons = '';

        foreach ($reasons as $reason) {
            $concatenatedReasons .= "{$reason->message} \r\n";
        }

        return $concatenatedReasons;
    }
}
