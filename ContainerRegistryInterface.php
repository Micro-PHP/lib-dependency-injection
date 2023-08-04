<?php

/*
 *  This file is part of the Micro framework package.
 *
 *  (c) Stanislau Komar <kost@micro-php.net>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Micro\Framework\DependencyInjection;

interface ContainerRegistryInterface
{
    /**
     * Register new service.
     *
     * @template T of Object
     *
     * @param class-string<T> $id      service alias
     * @param callable        $service service initialization callback
     */
    public function register(string $id, callable $service): void;
}
