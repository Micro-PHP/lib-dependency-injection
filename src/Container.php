<?php

namespace Micro\Component\DependencyInjection;

use Micro\Component\DependencyInjection\Exception\ServiceNotRegisteredException;
use Micro\Component\DependencyInjection\Exception\ServiceRegistrationException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface, ContainerRegistryInterface, ContainerDecoratorInterface
{
    /**
     * @var array<string, object>
     */
    private array $services = [];

    /**
     * @var array<string, \Closure|string>
     */
    private array $servicesRaw = [];

    /**
     * @var array<string, array<int, \Closure>>
     */
    private array $decorators = [];

    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    public function get(string $id): object
    {
        return $this->lookup($id);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return !empty($this->servicesRaw[$id]) || !empty($this->services[$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function register(string $id, \Closure $service): void
    {
        if($this->has($id)) {
            throw new ServiceRegistrationException(sprintf('Service "%s" already registered', $id));
        }

        $this->servicesRaw[$id] = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(string $id, \Closure $service, int $priority = 0): void
    {
        $this->decorators[$id][$priority][] = $service;
    }

    /**
     * @param  string $id
     *
     * @return object
     */
    private function lookup(string $id): object
    {
        if(!empty($this->services[$id])) {
            return $this->services[$id];
        }

        $this->initializeService($id);

        return $this->services[$id];
    }

    /**
     * @param string $serviceId
     */
    protected function initializeService(string $serviceId): void
    {
        if(empty($this->servicesRaw[$serviceId])) {
            throw new ServiceNotRegisteredException($serviceId);
        }

        $raw                        = $this->servicesRaw[$serviceId];
        $service                    = $raw($this);
        $this->services[$serviceId] = $service;

        if(!array_key_exists($serviceId, $this->decorators)) {
            return;
        }

        $decoratorsByPriority = $this->decorators[$serviceId];
        ksort($decoratorsByPriority);

        foreach ($decoratorsByPriority as $decorators) {
            foreach ($decorators as $decorator) {
                $this->services[$serviceId] = $decorator($this->services[$serviceId], $this);
            }
        }

        unset($this->decorators[$serviceId]);
    }
}
