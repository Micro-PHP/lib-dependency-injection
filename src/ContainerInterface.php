<?php

declare(strict_types=1);

/*
 *  This file is part of the Micro framework package.
 *
 *  (c) Stanislau Komar <kost@micro-php.net>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Micro\Component\DependencyInjection;

interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    /**
     * @param class-string $id
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    #[\Override]
    public function has(string $id): bool;

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @psalm-return T
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    #[\Override]
    public function get(string $id): mixed;
}
