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
final class ProxyFactory implements ProxyClassContentFactoryInterface
{
    public function __construct(
        private readonly ProxyClassNameGeneratorInterface $classNameGenerator,
    ) {
    }

    private function createNamespacedName(string $name): string
    {
        $isObjAlias = class_exists($name) || interface_exists($name);
        if (
            $isObjAlias && !str_starts_with($name, '\\')
        ) {
            return '\\'.$name;
        }

        return $name;
    }

    /**
     * @throws \ReflectionException
     */
    #[\Override]
    public function create(string $className): string
    {
        $ref = new \ReflectionClass($className);
        $isInterface = $ref->isInterface();
        if (
            !$isInterface
            && ($ref->isAbstract() || $ref->isFinal() || $ref->isEnum() || $ref->isAnonymous() || $ref->isTrait())
        ) {
            throw new \LogicException(\sprintf('`%s` should be non abstract, no enum, no final and not anonymous class or interface or trait.', $className));
        }
        $methods = [];
        foreach ($ref->getMethods() as $refMethod) {
            if (!$refMethod->isPublic() || $refMethod->isConstructor()) {
                continue;
            }

            $methods[] = $this->createProxyMethod($refMethod);
        }

        $methods = implode("\n", $methods);
        $proxyClass = $this->createProxyClass($ref, $methods, $isInterface);

        return $proxyClass;
    }

    private function createMethodArguments(\ReflectionMethod $refMethod): string
    {
        $arguments = [];
        foreach ($refMethod->getParameters() as $refParam) {
            $arg = trim($this->createTypes($refParam->getType()).' $'.$refParam->getName());
            try {
                $defaultValue = $refParam->getDefaultValueConstantName();
                if (null === $defaultValue) {
                    try {
                        $defaultValue = (string) $refParam->getDefaultValue();
                    } catch (\ReflectionException) {
                        $defaultValue = null;
                    }
                }

                if (null === $defaultValue || '' === $defaultValue) {
                    $defaultValue = 'null';
                }

                $arg .= ' = '.$defaultValue;
            } catch (\ReflectionException $exception) {
            }

            $arguments[$refParam->getPosition()] = $arg;
        }

        return implode(', ', $arguments);
    }

    private function createTypes(?\ReflectionType $refType): string
    {
        if (!$refType) {
            return 'mixed';
        }

        $allowNull = $refType->allowsNull();

        if ($refType instanceof \ReflectionNamedType) {
            $type = $this->createNamespacedName($refType->getName());
            if ($allowNull && !\in_array($type, ['null', 'mixed', 'void'], true)) {
                return 'null|'.$type;
            }

            return $type;
        }

        if ($refType instanceof \ReflectionUnionType || $refType instanceof \ReflectionIntersectionType) {
            $returnTypes = [];
            foreach ($refType->getTypes() as $returnType) {
                $returnTypes[] = $returnType instanceof \ReflectionNamedType
                    ? $this->createNamespacedName($returnType->getName())
                    : 'mixed';
            }

            if ($allowNull && !\in_array('null', $returnTypes, true)) {
                $returnTypes[] = 'null';
            }

            $separator = $refType instanceof \ReflectionUnionType ? '|' : '&';

            return implode($separator, $returnTypes);
        }

        return 'mixed';
    }

    private function createProxyMethod(\ReflectionMethod $method): string
    {
        $template = '
    public function %s(%s): %s
    {
        %s$this->_proxied()->%s(%s);
    }
          ';

        $returnTypes = $this->createTypes($method->getReturnType());
        $isVoid = 'void' === $returnTypes;
        $methodName = $method->getName();
        $arguments = [];
        foreach ($method->getParameters() as $parameter) {
            $arguments[] = '$'.$parameter->getName();
        }

        return \sprintf(
            $template,
            $methodName,
            $this->createMethodArguments($method),
            $returnTypes,
            $isVoid ? '' : 'return ',
            $methodName,
            implode(', ', $arguments),
        );
    }

    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $classRef
     */
    private function createProxyClassConstructor(\ReflectionClass $classRef): string
    {
        return \sprintf('return $this->container->get(\%s::class, true);', $classRef->getName());
        /*if($classRef->isInterface()) {
            return sprintf('return $this->container->get(\%s::class, true);', $classRef->getName());
        }

        $className = $classRef->getName();
        $constructor = $classRef->getConstructor();
        $refParameters = $constructor?->getParameters() ?: [];
        $parameters = [];
        foreach ($refParameters as $refParam) {
            $refType = $refParam->getType();
            if($refType instanceof \ReflectionUnionType) {
                throw new ServiceRegistrationException(
                    sprintf('Autowire union type is not possible. Class `%s`', $className)
                );
            }

            $refType = $refParam->getType();
            $parameters[] = sprintf('$this->container->get(\%s::class)', $refType->getName());
        }

        return sprintf(
            '$this->_proxiedObject = new \%s(%s);',$className, implode(',', $parameters)
        );
        */
    }

    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $classRef
     */
    private function createProxyClass(
        \ReflectionClass $classRef,
        string $methods,
        bool $isInterface,
    ): string {
        $template = "final %s class %s %s %s
{
    public function __construct(
        private \Psr\Container\ContainerInterface \$container
    ) {
    }
    
    private function _proxied(): %s
    {
        %s
    }
    %s
}
";
        $refName = $classRef->getName();
        $proxyClassName = $this->classNameGenerator->createShortProxyClassName($refName);
        $fullClassName = $this->classNameGenerator->getRealClassName($refName);

        return \sprintf(
            $template,
            $classRef->isReadOnly() ? 'readonly': '',
            $proxyClassName,
            $isInterface ? 'implements' : 'extends',
            $fullClassName,
            $fullClassName,
            $this->createProxyClassConstructor($classRef),
            $methods
        );
    }
}
