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

interface ContainerDecoratorInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $id
     */
    public function decorate(string $id, callable $service, int $priority = 0): void;
}
