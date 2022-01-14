<?php

namespace Micro\Component\DependencyInjection\Tests;

use Micro\Component\DependencyInjection\Container;
use Micro\Component\DependencyInjection\Exception\ServiceNotRegisteredException;
use Micro\Component\DependencyInjection\Exception\ServiceRegistrationException;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testContainerResolveDependencies(): void
    {
        $container = new Container();

        $container->register('test', function ( Container $container ) { return new class {
            public string $name = 'success';
        }; });

        $service = $container->get('test');
        $this->assertIsObject($service);
        $this->assertEquals('success', $service->name);
    }

    public function testRegisterTwoServicesWithEqualAliasesException(): void
    {
        $this->expectException(ServiceRegistrationException::class);
        $container = new Container();

        $container->register('test', function ( Container $container ) { return new class {}; });
        $container->register('test', function ( Container $container ) { return new class {}; });
    }

    public function testContainerUnresolvedException(): void
    {
        $this->expectException(ServiceNotRegisteredException::class);

        $container = new Container();
        $container->register('test', function ( Container $container ) { return new class {
            public string $name = 'success';
        }; });

        $container->get('test2');
    }
}
