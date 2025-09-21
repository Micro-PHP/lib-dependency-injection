<?php

/*
 *  This file is part of the Micro framework package.
 *
 *  (c) Stanislau Komar <kost@micro-php.net>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Micro\Component\DependencyInjection;

use Micro\Component\DependencyInjection\Exception\ServiceNotRegisteredException;
use Micro\Component\DependencyInjection\Exception\ServiceRegistrationException;

/**
 * @author Stanislau Komar <head.trackingsoft@gmail.com>
 */
class Container implements ContainerInterface, ContainerRegistryInterface, ContainerDecoratorInterface
{
    /**
     * @var array<class-string, mixed>
     */
    private array $services = [];

    /**
     * @var array<class-string, callable>
     */
    private array $servicesRaw = [];

    /**
     * @var array<class-string, array<int, array<int, callable>>>
     */
    private array $decorators = [];

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     * @psalm-suppress MixedPropertyTypeCoercion
     *
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @psalm-return T
     */
    #[\Override]
    public function get(string $id): object
    {
        if (!empty($this->services[$id])) {
            return $this->services[$id];
        }

        $this->initializeService($id);

        return $this->services[$id];
    }

    /**
     * @param class-string $id
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    #[\Override]
    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->servicesRaw)
            || \array_key_exists($id, $this->services);
    }

    #[\Override]
    public function register(string $id, callable $service, bool $force = false): void
    {
        if ($this->has($id) && !$force) {
            throw new ServiceRegistrationException(\sprintf('Service "%s" already registered', $id));
        }

        $this->servicesRaw[$id] = $service;
    }

    /**
     * @psalm-suppress InvalidPropertyAssignmentValue
     */
    #[\Override]
    public function decorate(string $id, callable $service, int $priority = 0): void
    {
        if (!\array_key_exists($id, $this->decorators)) {
            /**
             * @psalm-suppress PropertyTypeCoercion
             *
             * @phpstan-ignore-next-line
             */
            $this->decorators[$id] = [];
        }

        /**
         * @psalm-suppress PropertyTypeCoercion
         *
         * @phpstan-ignore-next-line
         */
        $this->decorators[$id][$priority][] = $service;
    }

    /**
     * @template T of Object
     *
     * @param class-string<T> $serviceId
     */
    protected function initializeService(string $serviceId): void
    {
        if (!isset($this->servicesRaw[$serviceId])) {
            throw new ServiceNotRegisteredException($serviceId);
        }

        $raw = $this->servicesRaw[$serviceId];
        $service = $raw($this);
        $this->services[$serviceId] = $service;

        if (!\array_key_exists($serviceId, $this->decorators)) {
            return;
        }

        $decoratorsByPriority = $this->decorators[$serviceId];
        krsort($decoratorsByPriority);

        foreach ($decoratorsByPriority as $decorators) {
            foreach ($decorators as $decorator) {
                $this->services[$serviceId] = $decorator($this->services[$serviceId], $this);
            }
        }

        unset($this->decorators[$serviceId]);
    }
}
