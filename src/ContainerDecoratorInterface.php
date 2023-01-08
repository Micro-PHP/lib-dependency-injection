<?php

namespace Micro\Component\DependencyInjection;

interface ContainerDecoratorInterface
{
    /**
     * @param string $id
     * @param \Closure $service
     * @param int $priority
     *
     * @return void
     */
    public function decorate(string $id, \Closure $service, int $priority = 0): void;
}
