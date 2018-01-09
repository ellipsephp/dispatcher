# Dispatcher

This package provides a basic **[Psr-15 middleware](https://github.com/http-interop/http-server-middleware)** dispatcher implementation.

**Require** php >= 7.1

**Installation** `composer require ellipse/dispatcher`

**Run tests** `./vendor/bin/kahlan`

- [Getting started](https://github.com/ellipsephp/dispatcher#getting-started)
- [Using a fallback response](https://github.com/ellipsephp/dispatcher#using-a-fallback-response)
- [Middleware and request handler resolving](https://github.com/ellipsephp/dispatcher#middleware-and-request-handler-resolving)
- [Composing a dispatcher](https://github.com/ellipsephp/dispatcher#composing-a-dispatcher)

## Getting started

This package provides an `Ellipse\Dispatcher` class allowing to process a Psr-7 request through a list of [Psr-15 middleware](https://github.com/http-interop/http-server-middleware) before handling it with a [Psr-15 request handler](https://github.com/http-interop/http-server-handler).

It is basically a Psr-15 request handler decorator wrapping a list of middleware around a request handler. Its constructor takes two parameters:

- a request handler instance implementing `Interop\Http\Server\RequestHandlerInterface`
- an `iterable` (array or implementation of `Traversable`) containing middleware instances implementing `Interop\Http\Server\MiddlewareInterface`

The `Dispatcher` itself implements `Interop\Http\Server\RequestHandlerInterface` so a Psr-7 response is produced by using its `->handle()` method with a Psr-7 request. It also means it can be used as the request handler of another `Dispatcher`.

The same `Dispatcher` can be used multiple times to handle as many request as needed. The only exception is when creating a `Dispatcher` using a php `Generator` as list of middleware: it could only be used one time because a php `Generator` can't be rewinded.

The middleware from the list are treated as a queue (first in first out) so the first middleware in the list is the first to process the request.

Finally if a middleware from the given list is not an implementation of `MiddlewareInterface` an `Ellipse\Dispatcher\Exceptions\MiddlewareTypeException` is thrown. You can use a [factory decorators](https://github.com/ellipsephp/dispatcher#middleware-and-request-handler-resolving) to resolve some type of values as Psr-15 middleware.

```php
<?php

namespace App;

use Ellipse\Dispatcher;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Create a dispatcher using two middleware and a request handler. The list of middleware can be an array or any implementation of Traversable.
$dispatcher = new Dispatcher(new SomeRequestHandler, [
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// Produce a response using the dispatcher. Here the request goes through SomeMiddleware1, SomeMiddleware2 and SomeRequestHandler.
$response = $dispatcher->handle($request);

// It can be used as the request handler of another dispatcher. Here the request goes through SomeMiddleware3, SomeMiddleware1, SomeMiddleware2 and SomeRequestHandler
(new Dispatcher($dispatcher, [new SomeMiddleware3]))->handle($request);

// Be careful here a MiddlewareTypeException is thrown because 'something' is not a Psr-15 middleware.
new Dispatcher(new SomeRequestHandler, [new SomeMiddleware, 'something']);
```

The `Dispatcher` class also have a `->with()` method taking an implementation of `MiddlewareInterface` as parameter. It returns a new dispatcher with the given middleware wrapped around the previous one. Be careful the new middleware will be the first processed by the new dispatcher:

```php
<?php

namespace App;

use Ellipse\Dispatcher;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Create a dispatcher with two middleware.
$dispatcher = new Dispatcher(new SomeRequestHandler, [
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// Create a new dispatcher with a new middleware on the top of the middleware list.
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

## Using a fallback response

A common practice is to define a fallback Psr-7 response to return when no middleware can produce a response on its own. It can be achieved by using a request handler taking this default Psr-7 response as parameter and returning it when its `->handle()` method is called. In order to spare the developper the creation of such a basic request handler, this package provides an `Ellipse\Dispatcher\FallbackResponse` class implementing this logic.

```php
<?php

namespace App;

use Ellipse\Dispatcher;
use Ellipse\Dispatcher\FallbackResponse;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Get some fallback Psr-7 response, here with a 404 status code.
$response = some_psr7_response_factory()->withStatus(404);

// Create a dispatcher using two middleware and a fallback response as request handler.
$dispatcher = new Dispatcher(new FallbackResponse($response), [
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// The request goes through SomeMiddleware1, SomeMiddleware2 then the fallback response is
// returned when none of them return a response on its own.
$response = $dispatcher->handle($request);
```

## Middleware and request handler resolving

Another common practice is to allow callables and class names registered in a container to be used as regular Psr-15 middleware/request handler.

For this purpose this package also provides an `Ellipse\DispatcherFactory` class allowing to produce `Dispatcher` instances. Its `__invoke()` method takes any value as request handler and an optional iterable list of middleware. An `Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException` exception is thrown when the given request handler is not an implementation of `RequestHandlerInterface`.

```php
<?php

namespace App;

use Ellipse\DispatcherFactory;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Get a dispatcher factory.
$factory = new DispatcherFactory;

// Use the factory to create a new Dispatcher
$dispatcher = $factory(new SomeRequestHandler, [new SomeMiddleware]);

// The request handler can be any value but a RequestHandlerTypeException is thrown when it is not an implementation of RequestHandlerInterface.
$dispatcher = $factory('something', [new SomeMiddleware]);
```

So what's the point of all this you may ask.

The point of `DispatcherFactory` is to be decorated by other factories resolving the given values as Psr-15 middleware/request handler before delegating the dispatcher creation to the decorated `DispatcherFactoryInterface` until it hits the original `DispatcherFactory`. It is a starting point for such factory decorators (also called resolvers) which ensure the `Dispatcher` creation fails when the request handler is not resolved as a Psr-15 request handler by any decorators.

Also don't forget implementations of `Traversable` can be used as middleware list so it is easy to wrap the given list inside an iterator resolving them on the fly.

Here is an example of callable resolving using the `Ellipse\Dispatcher\CallableResolver` class from the [ellipse/dispatcher-callable](https://github.com/ellipsephp/dispatcher-callable) package:

```php
<?php

namespace App;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\CallableResolver;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Get a decorated dispatcher factory.
$factory = new CallableResolver(new DispatcherFactory);

// A dispatcher using both callables and Psr-15 instances can now be created.
$middleware = function ($request, $handler) {

    // This callable behave like a Psr-15 middleware.

};

$handler = function ($request) {

    // This callable behave like a Psr-15 request handler.

};

$dispatcher = $factory($handler, [$middleware, new SomeMiddleware]);

// This works :-)
$dispatcher->handle($request);
```

Here is some ellipse packages providing resolvers for common resolving scenario:

- [ellipse/dispatcher-callable](https://github.com/ellipsephp/dispatcher-callable) allowing to use callables as Psr-15 instances
- [ellipse/dispatcher-container](https://github.com/ellipsephp/dispatcher-container) allowing to use Psr-15 instances retrieved from a [Psr-11 container](http://www.php-fig.org/psr/psr-11/meta/) using their class names
- [ellipse/dispatcher-controller](https://github.com/ellipsephp/dispatcher-controller) allowing to use controller definitions as Psr-15 request handler

Then it is up to you to build the dispatcher factory you need.

Here is an example of `DispatcherFactoryInterface` implementation in case you need to create a custom one. First we create a `IteratorAggregate` resolving middleware based on a clever condition:

```php
<?php

namespace App;

use IteratorAggregate;

class CleverMiddlewareGenerator implements IteratorAggregate
{
    private $middleware;

    public function __construct(iterable $middleware)
    {
        $this->middleware = $middleware;
    }

    public function getIterator()
    {
        // The decorated list of middleware is traversed and a CleverMiddleware is yielded instead of the original one when the clever condition is met.
        foreach ($this->middleware as $middleware) {

            yield $this->cleverCondition($middleware)
                ? new CleverMiddleware($middleware)
                : $middleware;

        }
    }

    private cleverCondition($middleware): bool
    {
        // ...
    }
}
```

Then we create a factory decorator implementing `DispatcherFactoryInterface`:

```php
<?php

namespace App;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;

class CleverResolver implements DispatcherFactoryInterface
{
    private $delegate;

    public function __construct(DispatcherFactoryInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        // wrap the middleware inside the clever middleware generator.
        $middleware = new CleverMiddlewareGenerator($middleware);

        // replace the handler with a CleverRequestHandler when the clever condition is met.
        if ($this->cleverCondition($handler)) {

            $handler = new CleverRequestHandler($handler);

        }

        // Delegate the dispatcher creation to the decorated factory.
        return ($this->delegate)($handler, $middleware);
    }

    private cleverCondition($handler): bool
    {
        // ...
    }
}
```

The `CleverResolver` can now decorate any implementation of `DispatcherFactoryInterface`:

```php
<?php

namespace App;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\CallableResolver;

$factory = new CleverResolver(
    new CallableResolver(
        new DispatcherFactory
    )
);
```

## Composing a dispatcher

Sometimes a dispatcher needs to be composed at runtime according to certain conditions. When routing for example: a solution is needed to build a dispatcher using different middleware lists and request handlers according to the matched route.

For this purpose this package provides an `Ellipse\Dispatcher\ComposableResolver` class. It can decorate any object implementing `Ellipse\DispatcherFactoryInterface` and can be decorated by `Ellipse\Dispatcher\ResolverWithMiddleware` instances.

Let's have an example using [FastRoute](https://github.com/nikic/FastRoute):

```php
<?php

namespace App;

use FastRoute\RouteCollector;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ComposableResolver;
use Ellipse\Dispatcher\ResolverWithMiddleware;

// Create a new ComposableResolver.
$factory = new ComposableResolver(new DispatcherFactory);

// Create a new FastRoute route collector.
$r = new RouteCollector(...);

// Those middleware will be used by all the dispatchers.
$factory = new ResolverWithMiddleware($factory, [
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// The dispatcher matching the GET / route will use SomeMiddleware1, SomeMiddleware2 and RequestHandler1.
$r->get('/', $factory(new RequestHandler1));

// Let's have a first route group.
$r->group('/group1', function ($r) use ($factory) {

    // SomeMiddleware3 is specific to this route group.
    $factory = new ResolverWithMiddleware($factory, [new SomeMiddleware3]);

    // The dispatcher matching the GET /group1/route1 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware3 and RequestHandler2.
    $r->get('/route1', $factory(new RequestHandler2));

    // The dispatcher matching the GET /group1/route2 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware3 and RequestHandler3.
    $r->get('/route2', $factory(new RequestHandler3));

});

// And a second route group.
$r->group('/group2', function ($r) use ($factory) {

    // SomeMiddleware4 is specific to this route group.
    $factory = new ResolverWithMiddleware($factory, [new SomeMiddleware4]);

    // The dispatcher matching the GET /group2/route1 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware4 and RequestHandler4.
    $r->get('/route1', $factory(new RequestHandler4));

    // Also, dispatcher factories take an optional middleware list as second parameter so middleware can be added on a per route basis.
    $r->get('/route2', $factory(new RequestHandler5, [
        new SomeMiddleware5,
    ]));

});
```

Ok it is a bit cumbersome so `ComposableResolver` and `ResolverWithMiddleware` both have a `->with()` method taking an iterable list of middleware as parameter. Here is the same example using the `->with()` method:

```php
<?php

namespace App;

use FastRoute\RouteCollector;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ComposableResolver;

// Create a new ComposableResolver.
$factory = new ComposableResolver(new DispatcherFactory);

// Create a new FastRoute route collector.
$r = new RouteCollector(...);

// Those middleware will be used by all the dispatchers.
$factory = $factory->with([
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// The dispatcher matching the GET / route will use SomeMiddleware1, SomeMiddleware2 and RequestHandler1.
$r->get('/', $factory(new RequestHandler1));

// Let's have a first route group.
$r->group('/group1', function ($r) use ($factory) {

    // SomeMiddleware3 is specific to this route group.
    $factory = $factory->with([new SomeMiddleware3]);

    // The dispatcher matching the GET /group1/route1 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware3 and RequestHandler2.
    $r->get('/route1', $factory(new RequestHandler2));

    // The dispatcher matching the GET /group1/route2 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware3 and RequestHandler3.
    $r->get('/route2', $factory(new RequestHandler3));

});

// And a second route group.
$r->group('/group2', function ($r) use ($factory) {

    // SomeMiddleware4 is specific to this route group.
    $factory = $factory->with([new SomeMiddleware4]);

    // The dispatcher matching the GET /group2/route1 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware4 and RequestHandler4.
    $r->get('/route1', $factory(new RequestHandler4));

    // Also, dispatcher factories take an optional middleware list as second parameter so middleware can be added on a per route basis.
    $r->get('/route2', $factory(new RequestHandler5, [
        new SomeMiddleware5,
    ]));

});
```

Of course, `ComposableResolver` can decorate any instance implementing `DispatcherFactoryInterface`. For example with the `CallableResolver` class from the [ellipse/dispatcher-callable](https://github.com/ellipsephp/dispatcher-callable) package:

```php
<?php

namespace App;

use FastRoute\RouteCollector;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ComposableResolver;
use Ellipse\Dispatcher\CallableResolver;

// Create a new ComposableResolver resolving callables.
$factory = new ComposableResolver(
    new CallableResolver(
        new DispatcherFactory
    )
);

// Create a new FastRoute route collector.
$r = new RouteCollector(...);

// Callables can be used as psr-15 middleware.
$factory = $factory->with([
    function ($request, $handler) {

        // This callable behave like a Psr-15 middleware.

    },
]);

// Callables can be used as request handlers too.
$r->get('/', $factory(function ($request) {

    // This callable behave like a Psr-15 request handler.

}))
```
