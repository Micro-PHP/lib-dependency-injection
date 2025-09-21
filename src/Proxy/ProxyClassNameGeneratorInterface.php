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

namespace Micro\Component\DependencyInjection\Proxy;

interface ProxyClassNameGeneratorInterface
{
    public function createProxyClassName(string $className): string;

    public function createShortProxyClassName(string $className): string;

    public function getRealClassName(string $className): string;
}
