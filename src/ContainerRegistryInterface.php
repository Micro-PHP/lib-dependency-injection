<?php

namespace Micro\Component\DependencyInjection;

interface ContainerRegistryInterface
{
    /**
     * Register new service.
     *
     * @param string   $id      service alias
     * @param \Closure $service service initialization callback
     *
     * @return void
     */
    public function register(string $id, \Closure $service): void;
}
