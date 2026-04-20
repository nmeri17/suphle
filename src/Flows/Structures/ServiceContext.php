<?php
namespace Suphle\Flows\Structures;

class ServiceContext
{
    public function __construct(
        /**
         * @property {serviceName} where we'll be pulling the data we intend to filter into another operation
         */
        public readonly string $serviceName,
        public readonly string $method
    ) {

        //
    }
}
