# Dispatcher

This package provides a basic **[Psr-15 middleware](https://github.com/http-interop/http-server-middleware)** dispatcher implementation.

**Require** php >= 7.1

**Installation** `composer require ellipse/dispatcher`

**Run tests** `./vendor/bin/kahlan`

- [Getting started](https://github.com/ellipsephp/dispatcher#getting-started)
- [Middleware and request handler resolving](https://github.com/ellipsephp/dispatcher#middleware-and-request-handler-resolving)
- [Composing a dispatcher](https://github.com/ellipsephp/dispatcher#composing-a-dispatcher)

## Getting started

This package provides an `Ellipse\Dispatcher` class allowing to process a Psr-7 request through a list of [Psr-15 middleware](https://github.com/http-interop/http-server-middleware) before handling it with a [Psr-15 request handler](https://github.com/http-interop/http-server-handler). Its constructor takes two parameters:

- an `iterable` (array or implementation of `Traversable`) containing middleware objects implementing `Interop\Http\Server\MiddlewareInterface`
- a final request handler object implementing `Interop\Http\Server\RequestHandlerInterface`

The `Dispatcher` itself implements `Interop\Http\Server\RequestHandlerInterface` so a Psr-7 response is produced by using its `->handle()` method with a Psr-7 request. It also means it can be used as the request handler of another `Dispatcher`.

The same `Dispatcher` can be used multiple times to handle as many request as needed. The only exception is when creating a `Dispatcher` using a php `Generator` as list of middleware: it could only be used one time because a php `Generator` can't be rewinded.

```php
<?php

namespace App;

use Ellipse\Dispatcher;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Create a dispatcher using two middleware and a request handler.
$dispatcher = new Dispatcher([
    new SomeMiddleware1,
    new SomeMiddleware2,
], new SomeRequestHandler);

// Produce a response using the dispatcher.
// The request goes through SomeMiddleware1, SomeMiddleware2 and SomeRequestHandler.
$response = $dispatcher->handle($request);

// It can be used as the request handler of another dispatcher.
// The request goes through SomeMiddleware3, SomeMiddleware1, SomeMiddleware2 and SomeRequestHandler
(new Dispatcher([new SomeMiddleware3], $dispatcher))->handle($request);
```

## Middleware and request handler resolving

A common practice is to allow callables and class names registered in a container to be used as regular Psr-15 middleware/request handler.

For this purpose this package also provides an `Ellipse\DispatcherFactory` class allowing to produce `Dispatcher` instances using any value as middleware/request handler. Exceptions are thrown if those values are not actual Psr-15 middleware/request handler at the time the `->handle()` method of the produced `Dispatcher` use them.

```php
<?php

namespace App;

use Ellipse\DispatcherFactory;

// Get some incoming Psr-7 request.
$request = some_psr7_request_factory();

// Get a dispatcher factory.
$factory = new DispatcherFactory;

// A dispatcher using any value as middleware and request handler can be created...
// Please note the request handler comes before the middleware because middleware are optional.
// We'll see why in the next section.
$dispatcher1 = $factory(new SomeRequestHandler, ['some_middleware']);
$dispatcher2 = $factory('some_request_handler');

// ... but here an Ellipse\Dispatcher\Exceptions\MiddlewareTypeException is thrown.
$dispatcher1->handle($request);

// ... but here an Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException is thrown.
$dispatcher2->handle($request);
```

So what's the point you may ask.

The point of `DispatcherFactory` is to be decorated by other factories resolving the given values as Psr-15 middleware/request handler before delegating the dispatcher creation to the original `DispatcherFactory`. It is a starting point for factory decorators (also called resolvers) which ensure the produced dispatcher would fail nicely when some values are not resolved as Psr-15 instances.

It is recommended for those factory decorators to implement `Ellipse\DispatcherFactoryInterface` so they can also be decorated by other decorators.

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

    // The dispatcher matching the GET /group1/route1 route will use SomeMiddleware1, SomeMiddleware2,
    // SomeMiddleware3 and RequestHandler2.
    $r->get('/route1', $factory(new RequestHandler2));

    // The dispatcher matching the GET /group1/route2 route will use SomeMiddleware1, SomeMiddleware2,
    // SomeMiddleware3 and RequestHandler3.
    $r->get('/route2', $factory(new RequestHandler3));

});

// And a second route group.
$r->group('/group2', function ($r) use ($factory) {

    // SomeMiddleware4 is specific to this route group.
    $factory = new ResolverWithMiddleware($factory, [new SomeMiddleware4]);

    // The dispatcher matching the GET /group2/route1 route will use SomeMiddleware1, SomeMiddleware2,
    // SomeMiddleware4 and RequestHandler4.
    $r->get('/route1', $factory(new RequestHandler4));

    // Also, dispatcher factories take an optional middleware list as second parameter so middleware
    // can be added on a per route basis.
    $r->get('/route2', $factory(new RequestHandler5, [
        new SomeMiddleware5,
    ]));

});
```

Ok it is a bit cumbersome so `ComposableResolver` and `ResolverWithMiddleware` both have a `->with()` method taking a list of middleware as parameter. Here is the same example using the `->with()` method:

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

    // The dispatcher matching the GET /group1/route1 route will use SomeMiddleware1, SomeMiddleware2,
    // SomeMiddleware3 and RequestHandler2.
    $r->get('/route1', $factory(new RequestHandler2));

    // The dispatcher matching the GET /group1/route2 route will use SomeMiddleware1, SomeMiddleware2,
    // SomeMiddleware3 and RequestHandler3.
    $r->get('/route2', $factory(new RequestHandler3));

});

// And a second route group.
$r->group('/group2', function ($r) use ($factory) {

    // SomeMiddleware4 is specific to this route group.
    $factory = $factory->with([new SomeMiddleware4]);

    // The dispatcher matching the GET /group2/route1 route will use SomeMiddleware1, SomeMiddleware2,
    // SomeMiddleware4 and RequestHandler4.
    $r->get('/route1', $factory(new RequestHandler4));

    // Also, dispatcher factories take an optional middleware list as second parameter so middleware
    // can be added on a per route basis.
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
