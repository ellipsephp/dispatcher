# Dispatcher

This package provides a **[Psr-15 middleware](https://github.com/http-interop/http-middleware) dispatcher**.

It allows to use resolvers for resolving both Psr-15 middleware and Psr-15 request handlers from any value at runtime.

**Require** php >= 7.1

**Installation** `composer require ellipse/dispatcher`

**Run tests** `./vendor/bin/kahlan --spec=tests`

- [Using the dispatcher](https://github.com/ellipsephp/dispatcher#using-the-dispatcher)

## Using the dispatcher

This package provides an `Ellipse\DispatcherFactory` class which takes two optional `callable` as parameters:

- The first one is used as a middleware resolver returning Psr-15 middleware from non Psr-15 middleware values
- The second one is a request handler resolver returning Psr-15 request handlers from non Psr-15 request handler values

Both are optional. Without them only objects implementing `Interop\Http\Server\MiddlewareInterface` and `Interop\Http\Server\RequestHandlerInterface` can be used with the produced dispatchers.

The `DispatcherFactory` instances are callables used to produce instances of `Ellipse\Dispatcher`. The first parameter is an iterable used as a list of middleware to process and the second one is the final request handler producing the last Psr-7 response.

```php
<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

class SomeMiddleware implements MiddlewareInterface
{
    public function process(ServcerRequestInterface $request, RequestHandlerInterface $handler)
    {
        // Return some Psr-7 response.
    }
}

class SomeOtherMiddleware implements MiddlewareInterface
{
    public function process(ServcerRequestInterface $request, RequestHandlerInterface $handler)
    {
        // Return some Psr-7 response.
    }
}
```

```php
<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\Server\RequestHandlerInterface;

class SomeRequestHandler implements RequestHandlerInterface
{
    public function handle(ServcerRequestInterface $request)
    {
        // Return some Psr-7 response.
    }
}
```

```php
<?php

namespace App;

use SomeContainer\Container;

use Ellipse\DispatcherFactory;

use App\Middleware\SomeMiddleware;
use App\Middleware\SomeOtherMiddleware;
use App\Handlers\SomeRequestHandler;

// Create a resolver retrieving string values from a container.
$container = new Container;

$container->set(SomeMiddleware::class, new SomeMiddleware);
$container->set(SomeOtherMiddleware::class, new SomeOtherMiddleware);
$container->set(SomeRequestHandler::class, new SomeRequestHandler);

$resolver = function ($element) use ($container) {

    if (is_string($element) && $container->has($element)) {

        return $container->get($element);

    }

}

// Create a dispatcher factory using the resolver for both middleware and request handler.
$factory = new DispatcherFactory($resolver, $resolver);

// Get a dispatcher using the dispatcher factory.
// Thanks to the resolver both actual Psr-15 objects and class names can be used as
// middleware and request handler. The resolver will try to resolve class names as
// Psr-15 instances at runtime.
$stack = [SomeMiddleware::class, new SomeOtherMiddleware];
$handler = SomeRequestHandler::class;

$dispatcher = $factory($stack, $handler);

// The dispatcher is itself a request handler and can be used to produce a Psr-7
// response from any Psr-7 request.
$request = some_psr7_request_factory();

$response = $dispatcher->handle($request);
```
