<?php

namespace Electrum\Request\Method;

use Electrum\Request\AbstractMethod;
use Electrum\Request\MethodInterface;
use Electrum\Response\Model\Version as VersionResponse;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
class Version extends AbstractMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $method = 'version';

    /**
     * @return VersionResponse
     *
     * @throws \Electrum\Request\Exception\BadRequestException
     * @throws \Electrum\Response\Exception\ElectrumResponseException
     */
    public function execute(array $optional = [])
    {
        $data = $this->getClient()->execute($this->method, $optional);
        return $this->hydrate(new VersionResponse(), $data);
    }
}