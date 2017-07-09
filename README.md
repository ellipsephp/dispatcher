# Dispatcher

This package provides a **Psr-15 middleware dispatcher**.

It allows to dispatch a Psr-7 request through a list of Psr-15 middleware in order to produce a Psr-7 response.

A resolver can be used to resolve any value composing the dispatcher as a Psr-15 middleware for extra flexibility.

Some useful resolvers are provided by ellipse packages:

- [ellipse/resolvers-callable](https://github.com/ellipsephp/resolvers-callable) resolves callables as middleware
- [ellipse/resolvers-container](https://github.com/ellipsephp/resolvers-container) resolves middleware class names as middleware using a Psr-11 container
- [ellipse/resolvers-action](https://github.com/ellipsephp/resolvers-action) resolve controller actions as middleware

Also, a [composite resolver](https://github.com/ellipsephp/resolvers-composite) can be used to combine two resolvers, hence allowing to produce a resolver combining any number of resolvers.

Please note that Psr-15 specification is not yet official and may be subject to changes.

**Require** php >= 7.1

**Installation** `composer require ellipse/dispatcher`

**Run tests** `./vendor/bin/peridot tests`

- [Dispatching a request](#dispatching-a-request)
- [Using a resolver](#using-a-resolver)

## Dispatching a request

This package provides a `Ellipse\Dispatcher\Dispatcher` class which can be instantiated with a list of Psr-15 middleware. The list can either be an array or a `Traversable` instance.

More middleware can be added using the `->with($middleware): Dispatcher` method. Please note `Dispatcher` instances are immutable so calling `->with()` will produce a new instance of the `Dispatcher` class containing the additional middleware, leaving the original dispatcher unmodified.

Once the dispatcher is built, the `->dispatch(Request $request): Response` method can be used to get the response produced by the dispatcher's middleware for the given request. It can be used as many time as needed with any Psr-7 request.

When the dispatcher's middleware do not produce a response, a `Ellipse\Dispatcher\Exceptions\NoResponseReturnedException` is thrown.

When a dispatcher's middleware produce anything else than a Psr-7 response, a `Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException` is thrown.

```php
<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\Dispatcher;
use App\SomeMiddleware;
use App\SomeOtherMiddleware;
use App\YetSomeOtherMiddleware;

// Get a dispatcher with a list of middleware.
$dispatcher1 = new Dispatcher([
    new SomeMiddleware,
    new SomeOtherMiddleware,
]);

// Get a new dispatcher using an aditional middleware.
$dispatcher2 = $dispatcher2->with(new YetSomeOtherMiddleware);

// Get an instance of ServerRequestInterface.
$request = get_server_request();

// Returns the response produced by SomeMiddleware and SomeOtherMiddleware for the given request.
$dispatcher1->dispatch($request);

// Returns the response produced by SomeMiddleware, SomeOtherMiddleware and YetSomeOtherMiddleware
// for the given request.
$dispatcher2->dispatch($request);
```

Please also note the `Dispatcher` class implements Psr-15 `MiddlewareInterface` as well so it can be added into another `Dispatcher` instance, or used with any other Psr-15 middleware dispatcher implementation.

```php
<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\Dispatcher;
use App\SomeMiddleware;
use App\SomeOtherMiddleware;
use App\YetSomeOtherMiddleware;

// This is a Psr-15 middleware.
$dispatcher = new Dispatcher([
    new SomeMiddleware,
    new SomeOtherMiddleware,
]);

// It can be added into another middleware dispatcher.
$dispatcher = new Dispatcher([$dispatcher, new YetSomeOtherMiddleware]);

// Get an instance of ServerRequestInterface.
$request = get_server_request();

// Returns the response produced by SomeMiddleware, SomeOtherMiddleware and YetSomeOtherMiddleware
// for the given request.
$dispatcher->dispatch($request);
```

Finally, an array or a `Traversable` instance containing middleware are converted into a `Dispatcher` instance when dispatching the request.

```php
<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\Dispatcher;
use App\SomeMiddleware;
use App\SomeOtherMiddleware;
use App\YetSomeOtherMiddleware;

// Middleware list can be nested.
$dispatcher = new Dispatcher([
    [new SomeMiddleware],
]);

// ->with() also accepts lists of middleware.
$dispatcher = $dispatcher->with([
    new SomeOtherMiddleware
    new YetSomeOtherMiddleware,
]);

// Get an instance of ServerRequestInterface.
$request = get_server_request();

// The lists are converted into dispatcher when using ->dispatch().
// This returns the response produced by SomeMiddleware, SomeOtherMiddleware and YetSomeOtherMiddleware
// for the given request.
$dispatcher->dispatch($request);
```

## Using a resolver

Sometimes it is useful to resolve regular values into Psr-15 middleware at the time the request is dispatched. For this purpose, the `Elipse\Dispatcher\Dispatcher` class can be instanciated with a resolver as a second parameter.

Any object can be used as a resolver as long as it implements the interface `Ellipse\Contracts\Resolver\ResolverInterface` from the [ellipse/contracts-resolver](https://github.com/ellipsephp/contracts-resolver) package.

Classes implementing `ResolverInterface` must only contain the `->resolve($element): MiddlewareInterface` method. It takes a value of any type as parameter and return a middleware when it is possible. When the element can't be resolved as a middleware, it should throw an exception implementing `Ellipse\Contracts\Resolver\Exception\ElementCantBeResolvedExceptionInterface`. In order to abstract this process, an `Ellipse\Resolvers\AbstractResolver` abstract class is provided by the [ellipse/resolvers-abstract](https://github.com/ellipsephp/resolvers-abstract) package.

Please note some useful resolvers are also provided by Ellipse packages, see [callable resolver](https://github.com/ellipsephp/resolvers-callable), [container resolver](https://github.com/ellipsephp/resolvers-container), [action resolver](https://github.com/ellipsephp/resolvers-action) and [composite resolver](https://github.com/ellipsephp/resolvers-composite).

```php
<?php

namespace App\Resolvers;

use RuntimeException;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

use Ellipse\Contracts\Resolver\ResolverInterface;
use Ellipse\Contracts\Resolver\Exceptions\ElementCantBeResolvedExceptionInterface;

use App\MyMiddleware;

class MyResolver implements ResolverInterface
{
    public function resolve($element): MiddlewareInterface
    {
        if ($element == 'mymiddleware') {

            return new MyMiddleware;

        }

        throw new class extends RuntimeException implements ElementCantBeResolvedExceptionInterface
        {
            //
        };
    }
}
```

```php
<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\Dispatcher;
use App\SomeMiddleware;
use App\MyResolver;

// By passing an instance of MyResolver to the dispatcher, the string 'mymiddleware' can be used
// as a middleware.
$dispatcher = new Dispatcher([new SomeMiddleware, 'myelement'], new MyResolver);

// Get an instance of ServerRequestInterface.
$request = get_server_request();

// The string 'mymiddleware' is resolved as an instance of MyMiddleware when the request is dispatched.
// Returns the response produced by SomeMiddleware and MyMiddleware for the given request.
$dispatcher->dispatch($request);
```

Finally, a new `Dispatcher` instance using a given resolver can be produced from an existing dispatcher using the `->withResolver(ResolverInterface $resolve): Dispatcher` method. The new `Dispatcher` instance contains the same middleware as the original instance.

```php
<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\Dispatcher;
use App\SomeMiddleware;
use App\MyResolver;

// Get a dispatcher containing a list of middleware.
$dispatcher = new Dispatcher([new SomeMiddleware, 'myelement']);

// Get an instance of ServerRequestInterface.
$request = get_server_request();

// This will throw an exception implementing Ellipse\Contracts\Resolver\Exceptions\ElementCantBeResolvedExceptionInterface
$dispatcher->dispatch($request);

// Produce a new dispatcher using MyResolver as resolver.
$dispatcher = $dispatcher->withResolver(new MyResolver);

// Returns the response produced by SomeMiddleware and MyMiddleware for the given request.
$dispatcher->dispatch($request);
```
