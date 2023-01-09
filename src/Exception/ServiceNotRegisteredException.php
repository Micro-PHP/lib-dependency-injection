<?php

namespace Micro\Component\DependencyInjection\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotRegisteredException extends \RuntimeException implements NotFoundExceptionInterface
{
    private string $serviceId;

    public function __construct(string $serviceId, int $code = 0, ?\Throwable $previous = null)
    {
        $this->serviceId = $serviceId;

        parent::__construct(sprintf('Service "%s" not registered.', $this->serviceId), $code, $previous);
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }
}
