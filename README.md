# Dispatcher

This package provides a [Psr-15](https://www.php-fig.org/psr/psr-15/) dispatcher implementation.

**Require** php >= 7.0

**Installation** `composer require ellipse/dispatcher`

**Run tests** `./vendor/bin/kahlan`

- [Using a dispatcher](#using-a-dispatcher)
- [Middleware and request handler resolving](#middleware-and-request-handler-resolving)

## Using a dispatcher

This package provides an `Ellipse\Dispatcher` class allowing to process a Psr-7 request through a Psr-15 middleware queue (First in first out order) before handling it with a Psr-15 request handler in order to create a Psr-7 response.

It is basically a request handler decorator wrapping a middleware queue around a request handler. Its constructor takes two parameters:

- a request handler object implementing `Psr\Http\Server\RequestHandlerInterface`
- an array containing middleware objects implementing `Psr\Http\Server\MiddlewareInterface`

The `Dispatcher` itself implements `RequestHandlerInterface` so a response is produced by using its `->handle()` method with a request. It also means it can be used as the request handler of another `Dispatcher`. Also, The same `Dispatcher` can be used multiple times to handle as many requests as needed.

Finally when a value of the given middleware queue is not an implementation of `MiddlewareInterface` an `Ellipse\Dispatcher\Exceptions\MiddlewareTypeException` is thrown. [Factory decorators](#middleware-and-request-handler-resolving) can be used to resolve some type of values as middleware.

```php
<?php

namespace App;

use Ellipse\Dispatcher;

// Create a dispatcher using two middleware and a request handler.
$dispatcher = new Dispatcher(new SomeRequestHandler, [
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// Here the request goes through SomeMiddleware1, SomeMiddleware2 and SomeRequestHandler.
$response = $dispatcher->handle($request);

// It can be used as the request handler of another dispatcher.
// Here the request goes through SomeMiddleware3, SomeMiddleware1, SomeMiddleware2 and SomeRequestHandler
(new Dispatcher($dispatcher, [new SomeMiddleware3]))->handle($request);

// Here a MiddlewareTypeException is thrown because 'something' is not a Psr-15 middleware.
new Dispatcher(new SomeRequestHandler, [new SomeMiddleware, 'something']);
```

The `Dispatcher` class also has a `->with()` method taking a `MiddlewareInterface` as parameter. It returns a new dispatcher with the given middleware wrapped around the current dispatcher. The new middleware will be the first processed by the new dispatcher:

```php
<?php

namespace App;

use Ellipse\Dispatcher;

// Create a dispatcher with two middleware.
$dispatcher = new Dispatcher(new SomeRequestHandler, [
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// Create a new dispatcher with a new middleware on the top of the middleware queue.
$dispatcher = $dispatcher->with(new SomeMiddleware3);

// Here the request goes through SomeMiddleware3, SomeMiddleware1, SomeMiddleware2 and SomeRequestHandler.
$response = $dispatcher->handle($request);

// It allows to create dispatchers from the outside-in if you like.
$dispatcher = new Dispatcher(new SomeRequestHandler);

$dispatcher = $dispatcher->with(new SomeMiddleware3);
$dispatcher = $dispatcher->with(new SomeMiddleware2);
$dispatcher = $dispatcher->with(new SomeMiddleware1);

// Here the request goes through SomeMiddleware1, SomeMiddleware2, SomeMiddleware3 and SomeRequestHandler.
$response = $dispatcher->handle($request);
```

## Middleware and request handler resolving

A common practice is to allow callables and class names registered in a container to be used as regular middleware/request handler.

For this purpose this package also provides an `Ellipse\DispatcherFactory` class implementing `Ellipse\DispatcherFactoryInterface`, allowing to produce `Dispatcher` instances. Its `__invoke()` method takes any value as request handler and an optional middleware queue. An `Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException` is thrown when the given request handler is not an implementation of `RequestHandlerInterface`.

```php
<?php

namespace App;

use Ellipse\DispatcherFactory;

// Get a dispatcher factory.
$factory = new DispatcherFactory;

// Use the factory to create a new Dispatcher.
$dispatcher = $factory(new SomeRequestHandler, [new SomeMiddleware]);

// Here a RequestHandlerTypeException is thrown because 'something' is not a Psr-15 request handler.
$dispatcher = $factory('something', [new SomeMiddleware]);

// Here a MiddlewareTypeException is thrown by the Dispatcher class because 'something' is not a Psr-15 middleware.
$dispatcher = $factory(new SomeRequestHandler, [new SomeMiddleware, 'something']);
```

This class is not very useful by itself. The point of `DispatcherFactory` is to be decorated by other factories resolving the given values as Psr-15 implementations before delegating it the dispatcher creation. It is a starting point for such factory decorators (also called resolvers) which ensure the dispatcher creation fails nicely when any value is not resolved as a Psr-15 implementation by any decorator.

Here is an example of callable resolving using the `Ellipse\Dispatcher\CallableResolver` class from the [ellipse/dispatcher-callable](https://github.com/ellipsephp/dispatcher-callable) package:

```php
<?php

namespace App;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\CallableResolver;

// Get a decorated dispatcher factory.
$factory = new CallableResolver(new DispatcherFactory);

// A dispatcher using both callables and Psr-15 implementations can now be created.
$middleware = function ($request, $handler) {

    // This callable behave like a Psr-15 middleware.

};

$handler = function ($request) {

    // This callable behave like a Psr-15 request handler.

};

// This works.
$response = $factory($handler, [$middleware, new SomeMiddleware])->handle($request);
```

Here is some ellipse packages providing resolvers for common resolving scenario:

- [ellipse/dispatcher-callable](https://github.com/ellipsephp/dispatcher-callable) allowing to use callables as Psr-15 middleware/request handlers
- [ellipse/dispatcher-container](https://github.com/ellipsephp/dispatcher-container) allowing to use Psr-15 middleware/request handler class names using a [Psr-11](https://www.php-fig.org/psr/psr-11/) container
- [ellipse/dispatcher-controller](https://github.com/ellipsephp/dispatcher-controller) allowing to use controller actions as Psr-15 request handlers using a [Psr-11](https://www.php-fig.org/psr/psr-11/) container

Here is an example of a class implementing `DispatcherFactoryInterface` in case you need to create a custom one:

```php
<?php

namespace App;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;

class MyResolver implements DispatcherFactoryInterface
{
    private $delegate;

    public function __construct(DispatcherFactoryInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    public function __invoke($handler, array $middleware = []): Dispatcher
    {
        // Replace the handler with a ResolvedRequestHandler when the request handler should be resolved.
        $handler = $this->shouldResolveHandler($handler)
            : new ResolvedRequestHandler($handler)
            ? $handler;

        // Replace middleware with a ResolvedMiddleware when a middleware should be resolved.
        $middleware = array_map(function ($middleware) {

            return $this->shouldResolveMiddleware($middleware)
                ? new ResolvedMiddleware($middleware)
                : $middleware;

        }, $middleware);

        // Delegate the dispatcher creation to the decorated factory.
        return ($this->delegate)($handler, $middleware);
    }

    private shouldResolveHandler($handler): bool
    {
        // ...
    }

    private shouldResolveMiddleware($middleware): bool
    {
        // ...
    }
}
```
