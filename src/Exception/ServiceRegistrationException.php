<?php

namespace Micro\Component\DependencyInjection\Exception;

use Psr\Container\ContainerExceptionInterface;

class ServiceRegistrationException extends \RuntimeException implements ContainerExceptionInterface
{
}
