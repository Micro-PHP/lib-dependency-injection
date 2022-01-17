<?php

namespace Micro\Component\DependencyInjection;


use Micro\Component\DependencyInjection\Exception\ServiceNotRegisteredException;
use Micro\Component\DependencyInjection\Exception\ServiceRegistrationException;
use Psr\Container\ContainerInterface;
use \Closure;

class Container implements ContainerInterface, ContainerRegistryInterface
{
    /**
     * @var array<string, object>
     */
    private array $services;

    /**
     * @var array<string, Closure>
     */
    private array $servicesRaw;


    public function __construct()
    {
        $this->services = [];
        $this->servicesRaw = [];
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $id)
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
        $this->servicesRaw[$id] = $service;
    }

    /**
     * @param  string $id
     * @return object
     */
    private function lookup(string $id): object
    {
        if(!empty($this->services[$id])) {
            return $this->services[$id];
        }

        return $this->createServiceInstance($id);
    }

    /**
     * @param  string $id
     * @return object
     */
    private function createServiceInstance(string $id): object
    {
        if(empty($this->servicesRaw[$id])) {
            throw new ServiceNotRegisteredException($id);
        }

        $raw = $this->servicesRaw[$id];
        $service = $raw($this);
        $this->services[$id] = $service;

        return $service;
    }
}
