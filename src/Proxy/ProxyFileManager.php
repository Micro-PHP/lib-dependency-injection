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
final readonly class ProxyFileManager implements ProxyFileManagerInterface
{
    public function __construct(
        private string $proxyClassFileDestination,
    ) {
    }

    #[\Override]
    public function writeProxiesFileContent(string $classesContent): void
    {
        file_put_contents(
            $this->proxyClassFileDestination,
            trim($classesContent)
        );
    }

    #[\Override]
    public function proxyBuildAlreadyExists(): bool
    {
        return file_exists($this->proxyClassFileDestination);
    }

    #[\Override]
    public function requireProxiesFile(): void
    {
        /** @psalm-suppress UnresolvableInclude */
        require_once $this->proxyClassFileDestination;
    }
}
