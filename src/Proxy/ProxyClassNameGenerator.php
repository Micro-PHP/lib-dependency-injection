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

/** @psalm-suppress UnusedClass */
final class ProxyClassNameGenerator implements ProxyClassNameGeneratorInterface
{
    /** @var array<string, string> */
    private array $classmapCache;

    public function __construct(private readonly string $proxyClassNamespace)
    {
        $this->classmapCache = [];
    }

    #[\Override]
    public function createProxyClassName(string $className): string
    {
        if (\array_key_exists($className, $this->classmapCache)) {
            return $this->classmapCache[$className];
        }

        $shortName = $this->createShortProxyClassName($className);
        if (!$this->proxyClassNamespace) {
            return $shortName;
        }

        $realName = '\\'.trim($this->proxyClassNamespace, '\\').'\\'.$shortName;
        $this->classmapCache[$className] = $realName;

        return $realName;
    }

    #[\Override]
    public function createShortProxyClassName(string $className): string
    {
        return str_replace('\\', '_', $className);
    }

    #[\Override]
    public function getRealClassName(string $className): string
    {
        return '\\'.ltrim($className, '\\');
    }
}
