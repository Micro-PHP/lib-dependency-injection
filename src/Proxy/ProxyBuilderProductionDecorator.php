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
final readonly class ProxyBuilderProductionDecorator implements ProxyBuilderInterface
{
    public function __construct(
        private ProxyBuilderInterface $proxyBuilder,
        private ProxyFileManagerInterface $proxyWriter,
    ) {
    }

    #[\Override]
    public function add(string $className): ProxyBuilderInterface
    {
        if ($this->proxyWriter->proxyBuildAlreadyExists()) {
            return $this;
        }

        $this->proxyBuilder->add($className);

        return $this;
    }

    #[\Override]
    public function build(): void
    {
        if (!$this->proxyWriter->proxyBuildAlreadyExists()) {
            $this->proxyBuilder->build();
        }

        $this->proxyWriter->requireProxiesFile();
    }
}
