<?php

namespace Micro\Component\DependencyInjection;


use Micro\Component\DependencyInjection\Autowire\AutowireHelperFactory;
use Micro\Component\DependencyInjection\Autowire\AutowireHelperFactoryInterface;
use Micro\Component\DependencyInjection\Exception\ServiceNotRegisteredException;
use Micro\Component\DependencyInjection\Exception\ServiceRegistrationException;
use \Closure;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface, ContainerRegistryInterface, ContainerDecoratorInterface
{
    /**
     * @var array<string, object>
     */
    private array $services;

    /**
     * @var array<string, Closure|string>
     */
    private array $servicesRaw;

    /**
     * @var array<string, array<Closure, int>>
     */
    private array $decorators = [];

    public function __construct(
    )
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
    public function register(string $id, Closure $service): void
    {
        if($this->has($id)) {
            throw new ServiceRegistrationException(sprintf('Service "%s" already registered', $id));
        }

        $this->servicesRaw[$id] = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(string $id, Closure $service, int $priority = 0): void
    {
        if(!array_key_exists($id, $this->decorators)) {
            $this->decorators[$id] = [];
        }

        $this->decorators[$id][] = [$service, $priority];
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
     * @return object
     */
    protected function initializeService(string $serviceId): void
    {
        if(empty($this->servicesRaw[$serviceId])) {
            throw new ServiceNotRegisteredException($serviceId);
        }

        $raw = $this->servicesRaw[$serviceId];
        $service = $raw($this);
        $this->services[$serviceId] = $service;

        if(!array_key_exists($serviceId, $this->decorators)) {
            return;
        }

        $decorators = $this->decorators[$serviceId];

        usort($decorators, function(array $left, array $right): int {
            $l = $left[1];
            $r = $right[1];
            if($l === $r) {
                return 0;
            }

            return $left[1] > $right[1] ? 1 : -1;
        });

        /** @var array<Closure, int> $decorator */
        foreach ($decorators as $decorator) {
            $this->services[$serviceId] = $decorator[0]();
        }
    }
}
