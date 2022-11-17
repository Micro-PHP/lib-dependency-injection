>**:heavy_exclamation_mark: Basic container implementation with the ability to inject dependencies**

### Requirements

PHP  >= 8.0.0

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

class Logger {
}

class Mailer {
    public function __construct(private Logger $logger) {}
}

$container = new Container();

$container->register(Logger::class, function(Container $container) {
    return new Logger();
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


class HelloWorldDecorator implements HelloWorldFacadeInterface
{
    public function __construct(
        private readonly HelloWorldFacadeInterface $decoratedService,
    )
    {
    }
    
    public function hello(string $name): string
    {
        $result = $this->decoratedService->hello($name);
        
        return $result . ' I\'m glad to see you';
    }
}

$container = new Container();

$container->register(HelloWorldFacadeInterface::class, function () {
    return new HelloWorldFacade();
});

$container->decorate(HelloWorldFacadeInterface::class, function(
    HelloWorldFacadeInterface $serviceForDecoration
) {
    return new HelloWorldLoggerAwareDecorator($serviceForDecoration);
});

echo $container->get(HelloWorldFacadeInterface::class)->hello('Stas');
// Output: Hello, Stas. I'm glad to see you


```


### Sample code for:

- [PSR-11](https://www.php-fig.org/psr/psr-11/)
