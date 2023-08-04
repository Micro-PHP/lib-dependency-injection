<?php

/*
 *  This file is part of the Micro framework package.
 *
 *  (c) Stanislau Komar <kost@micro-php.net>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Micro\Framework\DependencyInjection\Exception;

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
