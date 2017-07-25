# Dispatcher

This package provides a **Psr-15 middleware dispatcher**.

It allows to dispatch a Psr-7 request through a list of Psr-15 middleware in order to produce a Psr-7 response.

Please note that Psr-15 specification is not yet official and may be subject to changes.

**Require** php >= 7.1

**Installation** `composer require ellipse/dispatcher`

**Run tests** `./vendor/bin/peridot tests`

## Dispatching a request

This package provides a `Ellipse\Dispatcher\Dispatcher` class which can be instantiated with a list of Psr-15 middleware. The list can either be an array or a `Traversable` instance. It also takes an instance of `Interop\Http\ServerMiddleware\DelegateInterface` as an optional second parameter and it will be used as the final delegate.

Once the dispatcher is built, the `->process(Request $request): Response` method can be used to get the response produced by the dispatcher's middleware list for the given request. It can be used as many time as needed with any Psr-7 request.

When the dispatcher's middleware list do not produce a response, a `Ellipse\Dispatcher\Exceptions\NoResponseReturnedException` is thrown.

When one of the middleware produce anything else than a Psr-7 response, a `Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException` is thrown.

```php
<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\Dispatcher;
use App\SomeMiddleware;
use App\SomeOtherMiddleware;

// Get a dispatcher with a list of middleware.
$dispatcher = new Dispatcher([
    new SomeMiddleware,
    new SomeOtherMiddleware,
]);

// Get an instance of ServerRequestInterface.
$request = get_server_request();

// Returns the response produced by SomeMiddleware and SomeOtherMiddleware for the given request.
$dispatcher->dispatch($request);
```

Please also note the `Dispatcher` class implements Psr-15 `DelegateInterface` so it can be added into another `Dispatcher` instance, or used with any Psr-15 middleware.

```php
<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\Dispatcher;
use App\SomeMiddleware;
use App\SomeOtherMiddleware;
use App\YetSomeOtherMiddleware;

// This is a Psr-15 delegate.
$dispatcher = new Dispatcher([
    new SomeOtherMiddleware,
    new YetSomeOtherMiddleware,
]);

// It can be added into another middleware dispatcher as the final delegate.
$dispatcher = new Dispatcher([new SomeMiddleware], $dispatcher);

// Get an instance of ServerRequestInterface.
$request = get_server_request();

// Returns the response produced by SomeMiddleware, SomeOtherMiddleware and YetSomeOtherMiddleware
// for the given request.
$dispatcher->dispatch($request);
```
