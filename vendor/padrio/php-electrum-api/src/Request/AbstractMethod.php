<?php

namespace Electrum\Request;

use Electrum\Client;
use Electrum\Response\ResponseInterface;
use Zend\Hydrator\Reflection as ReflectionHydrator;
use Zend\Hydrator\Reflection;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
abstract class AbstractMethod
{

    /**
     * @var string
     */
    private $method = '';

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var Client
     */
    private $client = null;

    /**
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        if($client instanceof Client) {
            $this->setClient($client);
        } else {
            $this->setClient(new Client());
        }
    }

    /**
     * Hydrate returned api data into our custom response models
     *
     * @param ResponseInterface $object
     * @param array             $data
     * @param null              $hydrator
     *
     * @return ResponseInterface
     */
    public function hydrate(ResponseInterface $object, array $data, $hydrator = null)
    {
        if(!$hydrator instanceof Reflection) {
            $hydrator = new ReflectionHydrator();
        }

        return $hydrator->hydrate(
            $data,
            $object
        );
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return AbstractMethod
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

}