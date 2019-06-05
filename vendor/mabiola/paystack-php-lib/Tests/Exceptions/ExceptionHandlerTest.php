<?php

namespace MAbiola\Paystack\Exceptions;

use Illuminate\Http\Response;
use MAbiola\Paystack\Tests\BaseTestCase;

/**
 * Created by Malik Abiola.
 * Date: 17/02/2016
 * Time: 18:35
 * IDE: PhpStorm.
 */
class ExceptionHandlerTest extends BaseTestCase
{
    private $response;

    public function setUp()
    {
        parent::setUp();

        $this->response = new \stdClass();
    }

    public function testExceptionHandlerHandlesUnauthorizedError()
    {
        $this->response->message = 'Unauthorized';
        $exception = ExceptionHandler::handle(
            '',
            $this->response,
            Response::HTTP_UNAUTHORIZED
        );
        $this->assertInstanceOf(PaystackUnauthorizedException::class, $exception);
        $this->assertStringStartsWith('Unauthorized', $exception->getErrors());
    }

    public function testExceptionHandlerHandlesNotFoundErrors()
    {
        $this->response->message = 'Not Found';
        $exception = ExceptionHandler::handle(
            '',
            $this->response,
            Response::HTTP_NOT_FOUND
        );
        $this->assertInstanceOf(PaystackNotFoundException::class, $exception);
        $this->assertStringStartsWith($this->response->message, $exception->getErrors());
    }

    public function testExceptionHandlerHandlesBadRequestErrors()
    {
        $this->response->message = 'A validation error has occured';

        $exception = ExceptionHandler::handle(
            '',
            $this->response,
            Response::HTTP_BAD_REQUEST
        );
        $this->assertInstanceOf(PaystackValidationException::class, $exception);
        $this->assertStringStartsWith($this->response->message, $exception->getErrors());
    }

    public function testExceptionHandlerHandlesGateWayTimeoutExceptions()
    {
        $this->response->message = '';

        $exception = ExceptionHandler::handle(
            '',
            $this->response,
            Response::HTTP_GATEWAY_TIMEOUT
        );
        $this->assertInstanceOf(PaystackInternalServerError::class, $exception);
    }

    public function testExceptionHandlerHandlesInternalServerErrors()
    {
        $this->response->message = 'Internal Server Error';

        $exception = ExceptionHandler::handle(
            '',
            $this->response,
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
        $this->assertInstanceOf(PaystackInternalServerError::class, $exception);
    }

    public function testExceptionHandlerHandlesUnknownError()
    {
        $this->response->message = 'Internal Server Error';

        $exception = ExceptionHandler::handle(
            '',
            $this->response,
            Response::HTTP_SERVICE_UNAVAILABLE
        );
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
