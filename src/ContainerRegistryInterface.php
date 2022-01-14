<?php

namespace Micro\Component\DependencyInjection;


interface ContainerRegistryInterface
{
    /**
     * @param string $alias
     * @param \Closure $service
     * @return void
     */
    public function register(string $id, \Closure $service): void;
}
