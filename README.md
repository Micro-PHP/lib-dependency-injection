>**:heavy_exclamation_mark: Basic container implementation with the ability to inject dependencies**

### Requirements

PHP  >= 8.2

### How to use the library

Add the latest version of MicroDependencyInjection into your project by using Composer or manually:

__Using Composer (Recommended)__

Either run the following command in the root directory of your project:
```
composer require micro/dependency-injection
```

Or require the Checkout.com package inside the composer.json of your project:
```
"require": {
    "php": ">=8.0",
    "micro/dependency-injection": "dev-master"
},
```

### Example

After adding the library to your project, include the file autoload.php found in root of the library.
```html
include 'vendor/autoload.php';
```

#### Simple usage:
```php
use \Micro\Component\DependencyInjection\Container;

class Logger implements LoggerInterface {
}

class Mailer {
    public function __construct(private Logger $logger) {}
}

$container = new Container();

$container->register(LoggerInterface::class, function(Container $container) {
    return new Logger();
});

$container->register('logger.doctrine', function(Container $container) {
    return new Logger('doctrine-channel');
});

$container->register(Mailer::class, function(Container $container) {
    return new Mailer($container->get(Logger::class));
});

$mailer = $container->get(Mailer::class);
```

#### Service decoration.

```php
interface HelloWorldFacadeInterface
{
    public function hello(string $name): string;
}

class HelloWorldFacade implements HelloWorldFacadeInterface
{
    public function hello(string $name): string
    {
        return "Hello, {$name}.";
    }
}

class NiceHelloWorldDecorator implements HelloWorldFacadeInterface
{
    public function __construct(
        private readonly HelloWorldFacadeInterface $decoratedService
    ) {
    }
    
    public function hello(string $name): string
    {
        $result = $this->decoratedService->hello($name);
        
        return $result . ' I\'m glad to see you';
    }
}

class HelloWorldLoggerAwareDecorator implements HelloWorldFacadeInterface
{
    public function __construct(
        private readonly HelloWorldFacadeInterface $decoratedService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function hello(string $name): string
    {
        $result = $this->decoratedService->hello($name);
        
        $this->logger->info->info($result);
        
        return $result;
    }
}

$container = new Container();

$container->register(HelloWorldFacadeInterface::class, function () {
    return new HelloWorldFacade();
});

$container->register(HelloWorldFacadeInterface::class, function (
    HelloWorldFacadeInterface $decorated
) {
    return new NiceHelloWorldDecorator($decorated);
});

$container->decorate(HelloWorldFacadeInterface::class, function(
    HelloWorldFacadeInterface $decorated,
    Container $container
) {
    return new HelloWorldLoggerAwareDecorator(
        $decorated,
        $container->get(LoggerInterface::class)
    );
});

echo $container->get(HelloWorldFacadeInterface::class)->hello('Stas');
// Output: Hello, Stas. I'm glad to see you

```

### Sample code for:

- [PSR-11](https://www.php-fig.org/psr/psr-11/)
