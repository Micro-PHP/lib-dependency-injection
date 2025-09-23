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

use Micro\Component\DependencyInjection\Exception\ServiceRegistrationException;
use Micro\Component\DependencyInjection\Proxy\ProxyBuilderInterface;
use Micro\Component\DependencyInjection\Proxy\ProxyClassNameGeneratorInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/** @psalm-suppress UnusedClass */
final class ContainerCompiled extends Container implements ContainerRegistryCompiledInterface
{
    private bool $isCompiled;

    private PsrContainerInterface $decorated;

    public function __construct(
        private readonly ProxyClassNameGeneratorInterface $classNameGenerator,
        private readonly ProxyBuilderInterface $proxyBuilder,
        ?PsrContainerInterface $decorated = null,
    ) {
        $this->isCompiled = false;
        $this->decorated = $decorated ?? new Container();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @return T
     */
    #[\Override]
    public function get(string $id): object
    {
        $proxyClass = $this->classNameGenerator->createProxyClassName($id);
        if (!class_exists($proxyClass)) {
            /* @psalm-suppress MixedReturnStatement */
            return $this->decorated->get($id);
        }

        /**
         * @var T $instance
         *
         * @psalm-suppress MixedMethodCall
         */
        $instance = new $proxyClass($this->decorated);

        return $instance;
    }

    #[\Override]
    public function has(string $id): bool
    {
        return $this->decorated->has($id);
    }

    #[\Override]
    public function register(string $id, callable $service, bool $force = false): void
    {
        if (!$this->decorated instanceof ContainerRegistryInterface) {
            throw new ServiceRegistrationException('Container can not decorate service because container does not implement '.ContainerRegistryInterface::class);
        }

        if ($this->isCompiled) {
            throw new ServiceRegistrationException('Can not register service because container already compiled.');
        }

        if (interface_exists($id) || class_exists($id)) {
            $this->proxyBuilder->add($id);
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        $this->decorated->register($id, $service, $force);
    }

    /**
     * @psalm-suppress InvalidPropertyAssignmentValue
     */
    #[\Override]
    public function decorate(string $id, callable $service, int $priority = 0): void
    {
        if (!$this->decorated instanceof ContainerDecoratorInterface) {
            throw new ServiceRegistrationException('Container can not decorate service because container does not implement '.ContainerDecoratorInterface::class);
        }

        if ($this->isCompiled) {
            throw new ServiceRegistrationException('Can not register service because container already compiled.');
        }

        if (interface_exists($id) || class_exists($id)) {
            $this->proxyBuilder->add($id);
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        $this->decorated->decorate($id, $service, $priority);
    }

    #[\Override]
    public function compile(): void
    {
        $this->proxyBuilder->build();

        $this->isCompiled = true;
    }
}
