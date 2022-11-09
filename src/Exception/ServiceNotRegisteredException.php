<?php

namespace Micro\Component\DependencyInjection\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotRegisteredException extends \RuntimeException
    implements NotFoundExceptionInterface
{
}
