<?php

namespace Micro\Component\DependencyInjection\Tests;

use Micro\Component\DependencyInjection\Autowire\ContainerAutowire;
use Micro\Component\DependencyInjection\Container;
use Micro\Component\DependencyInjection\Exception\ServiceNotRegisteredException;
use Micro\Component\DependencyInjection\Exception\ServiceRegistrationException;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testContainerResolveDependencies(): void
    {
        $container = new Container();

        $container->register('test', function () {
            return new NamedService('success');
        });

        /** @var NamedInterface $service */
        $service = $container->get('test');
        $this->assertIsObject($service);
        $this->assertInstanceOf(NamedInterface::class, $service);
        $this->assertInstanceOf(NamedService::class, $service);
        $this->assertEquals('success', $service->getName());
    }

    public function testRegisterTwoServicesWithEqualAliasesException(): void
    {
        $this->expectException(ServiceRegistrationException::class);
        $container = new Container();

        $container->register('test', function () { return new class {}; });
        $container->register('test', function () { return new class {}; });
    }

    public function testContainerUnresolvedException(): void
    {
        $this->expectException(ServiceNotRegisteredException::class);

        $container = new Container();
        $container->register('test', function () {
            return new NamedService('success');
        });

        $container->get('test2');
    }

    public function testDecorateService(): void
    {
        $container = new Container();

        $container->register('test', function () {
            return new NamedService('A');
        });

        $container->decorate('test', function (NamedInterface $decorated) {
            return new NamedServiceDecorator($decorated, 'D');
        });

        $container->decorate('test', function (NamedInterface $decorated) {
            return new NamedServiceDecorator($decorated, 'B');
        }, 10);

        $container->decorate('test', function (NamedInterface $decorated) {
            return new NamedServiceDecorator($decorated, 'C');
        }, 5);

        /** @var NamedInterface $result */
        $result = $container->get('test');
        $this->assertInstanceOf(NamedServiceDecorator::class, $result);
        $this->assertInstanceOf(NamedInterface::class, $result);
        $this->assertEquals('ABCD', $result->getName());
    }

    public function testDecoratorsWithSamePriority(): void
    {
        $container = new Container();

        $container->register('test', function () {
            return new NamedService('A');
        });

        $container->decorate('test', function (NamedInterface $decorated) {
            return new NamedServiceDecorator($decorated, 'B');
        }, 10);

        $container->decorate('test', function (NamedInterface $decorated) {
            return new NamedServiceDecorator($decorated, 'D');
        });

        $container->decorate('test', function (NamedInterface $decorated) {
            return new NamedServiceDecorator($decorated, 'E');
        });

        $container->decorate('test', function (NamedInterface $decorated) {
            return new NamedServiceDecorator($decorated, 'C');
        }, 10);

        /** @var NamedInterface $result */
        $result = $container->get('test');
        $this->assertInstanceOf(NamedServiceDecorator::class, $result);
        $this->assertInstanceOf(NamedInterface::class, $result);
        $this->assertEquals('ABCDE', $result->getName());
    }
}

interface NamedInterface
{
    public function getName(): string;
}

readonly class NamedService implements NamedInterface
{
    public function __construct(private string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }
}

readonly class NamedServiceDecorator implements NamedInterface
{
    public function __construct(
        private object $decorated,
        private string $name
    ) {
    }

    public function getName(): string
    {
        return $this->decorated->getName().$this->name;
    }
}
