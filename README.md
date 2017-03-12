# Dispatcher
This package is a **Psr-15 middleware dispatcher** requiring **php 7.1**.

The terms used in the following documentation:
* *middleware* represents objects implementing [Psr-15](https://github.com/http-interop/http-middleware) `MiddlewareInterface`.
* *delegate* represents objects implementing [Psr-15](https://github.com/http-interop/http-middleware) `DelegateInterface`.
* *request* represents objects implementing [Psr-7](https://github.com/php-fig/http-message) `ServerRequestInterface`.
* *response* represents objects implementing [Psr-7](https://github.com/php-fig/http-message) `ResponseInterface`.
* *element* represents any value.

This package provides a `Dispatcher` class which can be used to dispatch a
request through a list of psr-15 middleware in order to produce a response.
See [getting started](#getting-started).

Any element can be dispatched as long as they can be resolved as a middleware by
the resolver used by the dispatcher. See [adding elements](#adding-elements).

Ellipse packages provide some useful resolvers but any object can be used as a
resolver as long as it implements the interface `Ellipse\Contracts\Resolver\ResolverInterface`
from the [ellipse/contracts-resolver](https://github.com/ellipsephp/contracts-resolver)
package. Many resolvers can be chained so they can be used together.
See [resolvers](#resolvers).

Please note that Psr-15 specification is not yet official and may be subject to
changes.

**Require** php >= 7.1

**Installation** `composer require ellipse/dispatcher`

**Run tests** `./vendor/bin/peridot tests`

* [Getting started](#getting-started)
* [Adding elements](#adding-elements)
* [Dispatcher interface](#dispatcher-interface)
* [Resolvers](#resolvers)
    * [Resolver interface](#resolver-interface)
    * [Abstract resolver](#abstract-resolver)
    * [Callable resolver](#callable-resolver)
    * [Container resolver](#container-resolver)
    * [Action resolver](#action-resolver)
    * [Recursive resolver](#recursive-resolver)
    * [Using multiple resolvers](#using-multiple-resolvers)

## Getting started
A dispatcher can be instantiated with an optional list of elements to dispatch
and an optional resolver to use. The elements list can either be an array or a
`Traversable` instance. An element can be either a middleware instance or
anything that can be resolved as a middleware by the given resolver. When no
resolver is specified, only middleware can be dispatched.

```php
<?php

namespace App;

use Ellipse\Dispatcher\Dispatcher;

// Can be instantiated without anything.
$dispatcher = new Dispatcher;

// A list of elements to dispatch can be given on instantiation.
$dispatcher = new Dispatcher([
    $middleware1,
    $middleware2,
    $middleware3,
]);

// A Traversable instance is ok as well.
$dispatcher = new Dispatcher(new \ArrayObject([
    $middleware1,
    $middleware2,
    $middleware3,
]));

// A revolver may aslo be given on instantiation.
$dispatcher = new Dispatcher([
    $middleware1,
    $middleware2,
    $middleware3,
], new SomeResolver);
```

Once the `Dispatcher` is built it can dispatch requests and return the response
produced by the given list of elements.

```php
<?php

namespace App;

use Ellipse\Dispatcher\Dispatcher;

$dispatcher = new Dispatcher([
    $middleware1,
    $middleware2,
    $middleware3,
]);

// Get the response produced by $middleware1, 2, and 3 for the given request.
$response = $dispatcher->dispatch($request);
```

Please also note `Dispatcher` class implements Psr-15 `MiddlewareInterface` as
well so instances of `Dispatcher` can be pushed into another instance of
`Dispatcher`, or into any other Psr-15 middleware dispatcher.

```php
<?php

namespace App;

use Ellipse\Dispatcher\Dispatcher;

// This is a Psr-15 middleware.
$dispatcher1 = new Dispatcher([
    $middleware1,
    $middleware2,
    $middleware3,
]);

// It can be pushed into another middleware dispatcher.
$dispatcher2 = new Dispatcher([
    $dispatcher1,
    $middleware4,
    $middleware5
]);

// Get the response produced by $middleware1, 2, 3, 4 and 5 for the given
// request.
$response = $dispatcher2->dispatch($request);
```

## Adding elements
Middleware can be added to the dispatcher after its instantiation using the
`with` method.

```php
<?php

namespace App;

use Ellipse\Dispatcher\Dispatcher;

$dispatcher = (new Dispatcher)->with($middleware);
```

Note `Dispatcher` instances are immutable, so calling `with` method returns a
new `Dispatcher` instance leaving the calling `Dispatcher` instance unmodified.

```php
<?php

namespace App;

use Ellipse\Dispatcher\Dispatcher;

$dispatcher1 = new Dispatcher([
    $middleware1,
    $middleware2,
]);

$dispatcher2 = $dispatcher1->with($middleware3);

// Get the response produced by $middleware1, and 2 for the given request.
$response = $dispatcher1->dispatch($request);

// Get the response produced by $middleware1, 2, and 3 for the given request.
$response = $dispatcher2->dispatch($request);
```

Any element can be added to the dispatcher as long as it can be resolved to a
middleware by the given resolver.

```php
<?php

namespace App;

use Ellipse\Dispatcher\Dispatcher;

$dispatcher = new Dispatcher([
    $element1,
    $element2,
], new SomeResolver);

$dispatcher = $dispatcher->with($element3);

// Resolve element1, 2 and 3 to middleware and get the response they produce.
$response = $dispatcher->dispatch($request);
```

## Dispatcher interface
`Dispatcher` instances implements the interface `Ellipse\Contracts\Dispatcher\DispatcherInterface`
provided by the [ellipse/contracts-dispatcher](https://github.com/ellipsephp/contracts-dispatcher)
package.

This interface is not intended to be implemented by other dispatcher. Its
purpose is just to be a contract for packages using ellipse dispatcher.

For example, it allows resolver packages to provides an implementation of `DispatcherInterface`
using the resolver provided by their service providers. See
[callable resolver](#callable-resolver),
[container resolver](#container-resolver),
[action resolver](#action-resolver) and
[recursive resolver](#recursive-resolver).

## Resolvers
* [Resolver interface](#resolver-interface)
* [Abstract resolver](#abstract-resolver)
* [Callable resolver](#callable-resolver)
* [Container resolver](#container-resolver)
* [Action resolver](#action-resolver)
* [Recursive resolver](#recursive-resolver)
* [Using multiple resolvers](#using-multiple-resolvers)

### Resolver interface
A resolver usable by `Dispatcher` instances is any object implementing
`Ellipse\Contracts\Resolver\ResolverInterface` from the
[ellipse/contracts-resolver](https://github.com/ellipsephp/contracts-resolver)
package.

`ResolverInterface` use the chain of responsibility pattern so many resolvers
can be chained. It define two methods:

* `->resolve($element): MiddlewareInterface`: it takes a value of any type as
  parameter and return a Psr-15 middleware when it is possible. Otherwise it
  should call and return the value produced by it's delegate resolver. When no
  delegate resolver is specified, it should throw an exception implementing
  `Ellipse\Contracts\Resolver\Exception\ElementCantBeResolvedExceptionInterface`.
* `->withDelegate(ResolverInterface $delegate): ResolverInterface`: it takes
  another resolver as parameter and should return a new resolver using the given
  resolver as delegate.

```php
<?php

namespace App\Resolvers;

use RuntimeException;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

use Ellipse\Contracts\Resolver\ResolverInterface;
use Ellipse\Contracts\Resolver\Exceptions\ElementCantBeResolvedExceptionInterface;

class ElementCantBeResolvedException extends RuntimeException implements ElementCantBeResolvedExceptionInterface
{
    //
}

class MyResolver implements ResolverInterface
{
    private $delegate;

    public function __construct(ResolverInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    public function resolve($element): MiddlewareInterface
    {
        if ($this->canResolve($element)) {

            return $this->getMiddleware($element);

        }

        if (! is_null($this->delegate)) {

            return $this->delegate->resolve($element);

        }

        throw new ElementCantBeResolvedException($element);
    }

    public function withDelegate(ResolverInterface $delegate): ResolverInterface
    {
        return new MyResolver($delegate);
    }

    private function canResolve($element): bool
    {
        // return whether the element can be resolved.
    }

    private function getMiddleware($element): MiddlewareInterface
    {
        // return some MiddlewareInterface instance from $element.
    }
}
```
Please note:

* In order to abstract this process, an `AbstractResolver` class is available
  from the [ellipse/resolvers-abstract](https://github.com/ellipsephp/resolvers-abstract)
  package. See [abstract resolver](#abstract-resolver).
* The recommended way of resolving many element types is to create one resolver
  for each single type of element and then to chain them together using the
  `withDelegate` method.
* Some useful resolvers are provided by Ellipse packages, see
  [callable resolver](#callable-resolver),
  [container resolver](#container-resolver),
  [action resolver](#action-resolver)
  and [recursive resolver](#recursive-resolver).

### Abstract resolver
To ease the creation of resolver classes, an `AbstractResolver` class is
provided by the [ellipse/resolvers-abstract](https://github.com/ellipsephp/resolvers-abstract)
package. Just two method must be defined when extending `AbstractResolver`, the
`canResolve` method and the `getMiddleware` method.

```php
<?php

namespace App\Resolvers;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

use Ellipse\Resolvers\AbstractResolver;

class MyResolver extends AbstractResolver
{
    public function canResolve($element): bool
    {
        // return whether the element can be resolved.
    }

    public function getMiddleware($element): MiddlewareInterface
    {
        // return some middleware from $element.
    }
}
```

### Callable resolver
The package [ellipse/resolvers-callable](https://github.com/ellipsephp/resolvers-callable)
contains a service provider named `CallableResolverServiceProvider` which
provides three implementations:

* one implementation of `CallableResolver`.
* one implementation of `ResolverInterface` with a `CallableResolver` added at
  the end of the chain.
* one implementation of `DispatcherInterface` using this resolver.

The `CallableResolver` class allows to resolve any callable as a middleware. In
order to work properly those callable values should take a `$request` and a
`$delegate` parameter and return a response.

`CallableResolverServiceProvider` implements [interop service provider](https://github.com/container-interop/container-interop)
so it should be used with containers that can handle them. For example [simplex](https://github.com/mnapoli/simplex).

```php
<?php

namespace App;

use Ellipse\Contracts\Resolver\ResolverInterface;

use Ellipse\Resolvers\CallableResolverServiceProvider;

use Ellipse\Dispatcher\Dispatcher;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service provider.
$container->register(CallableResolverServiceProvider::class);

// Now a resolver using callable resolver is available.
$resolver = $container->get(ResolverInterface::class);

// Callable elements can now be dispatched using this resolver.
$some_callable = function ($request, $delegate) { // ... returns a response };

$dispatcher = new Dispatcher([$some_callable], $resolver);

// The given request is processed by $some_callable.
$response = $dispatcher->dispatch($request);
```

`DispatcherInterface` implementation can also be used directly:

```php
<?php

namespace App;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;

use Ellipse\Resolvers\CallableResolverServiceProvider;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service provider.
$container->register(CallableResolverServiceProvider::class);

// Now a dispatcher using callable resolver is available.
$some_callable = function ($request, $delegate) { // ... returns a response };

$dispatcher = $container->get(DispatcherInterface::class)->with($some_callable);

// The given request is processed by $some_callable.
$response = $dispatcher->dispatch($request);
```

### Container resolver
The package [ellipse/resolvers-container](https://github.com/ellipsephp/resolvers-container)
contains a service provider named `ContainerResolverServiceProvider` which
provides three implementations:

* one implementation of `ContainerResolver`.
* one implementation of `ResolverInterface` with a `ContainerResolver` added at
  the end of the chain.
* one implementation of `DispatcherInterface` using this resolver.

Sometimes it is useful to retrieve middleware from the application container
instead of pushing an actual instance of the middleware. This can easily be
achieved using the `ContainerResolver`.

`ContainerResolverServiceProvider` implements [interop service provider](https://github.com/container-interop/container-interop)
so it should be used with containers that can handle them. For example [simplex](https://github.com/mnapoli/simplex).

```php
<?php

namespace App;

use Ellipse\Contracts\Resolver\ResolverInterface;

use Ellipse\Resolvers\ContainerResolverServiceProvider;

use Ellipse\Dispatcher\Dispatcher;

use App\Middleware\SomeMiddleware;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service provider.
$container->register(ContainerResolverServiceProvider::class);

// Now a resolver using container resolver is available.
$resolver = $container->get(ResolverInterface::class);

// Middleware class names registered in the container can now be dispatched
// using this resolver.
$container->set(SomeMiddleware::class, function () {

    // return an implementation of SomeMiddleware
    return new SomeMiddleware(...);

});

$dispatcher = new Dispatcher([SomeMiddleware::class], $resolver);

// The given request is processed by SomeMiddleware.
$response = $dispatcher->dispatch($request);
```

`DispatcherInterface` implementation can also be used directly:

```php
<?php

namespace App;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;

use Ellipse\Resolvers\ContainerResolverServiceProvider;

use App\Middleware\SomeMiddleware;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Now a dispatcher using container resolver is available.
$container->set(SomeMiddleware::class, function () {

    // return an implementation of SomeMiddleware
    return new SomeMiddleware(...);

});

$dispatcher = $container->get(DispatcherInterface::class)
    ->with(SomeMiddleware::class);

// The given request is processed by SomeMiddleware.
$response = $dispatcher->dispatch($request);
```

### Action resolver
The package [ellipse/resolvers-action](https://github.com/ellipsephp/resolvers-action)
contains a service provider named `ActionResolverServiceProvider` which provides
three implementations:

* one implementation of `ActionResolver`.
* one implementation of `ResolverInterface` with an `ActionResolver` added at
  the end of the chain.
* one implementation of `DispatcherInterface` using this resolver.

The `ActionResolver` class provides an easy way to use actions as middleware.
Actions are instances of `Ellipse\Resolvers\Action` which takes two parameters
on instantiation:

* A formatted string containing the action's class name and method to execute
  separated by @. Ex: `'\App\Controllers\SomeController@index'`.
* An optional array of parameters to use when executing the action. Usually it
  is values matching some url pattern.

Many things to note:

* When the action is processed as a middleware, an implementation of the
  action's class is retrieved from the container if it contains it. Otherwise
  when the action's class is instantiated all its constructor parameters are
  retrieved from the container based on their type hints. One exception: when
  there is a parameter type hinted as `Psr\Http\Message\ServerRequestInterface`,
  the request currently processed by the middleware is injected. The action's
  parameters are also injected for parameters without a class type hint, firstly
  matching the parameter names with the action's parameters array keys, then by
  their order.
* When the action's method is executed, all its parameters values are injected
  the same way as for the action's class constructor described above.
* Actions are expected to return a response as no delegate will be passed to
  the class method.
* When a `'resolvers.action.controllers_namespace'` alias is registered in the
  container, its value will be prepended to all action's class names.
* `ActionResolver` gets it's full power when used with the ellipse router which
  will hopefully  be released soon.

`ActionResolverServiceProvider` implements [interop service provider](https://github.com/container-interop/container-interop)
so it should be used with containers that can handle them. For example [simplex](https://github.com/mnapoli/simplex).

```php
<?php

namespace App\Controllers;

use App\Services\SomeService;
use App\Http\ResponseFactory;

class SomeController
{
    private $some_service;
    private $response_factory;

    public function __construct(SomeService $some_service, ResponseFactory $response_factory)
    {
        // Those values are retrieved from the container based on their class
        // type hints.
        $this->some_service = $some_service;
        $this->response_factory = $response_factory
    }

    public function index(ServerRequestInterface $request)
    {
        // $request is the request available at the time the middleware is being
        // processed.

        // some processing ...

        // returns a response.
        return $this->response_factory->createResponse();
    }
}
```

```php
<?php

namespace App;

use Ellipse\Contracts\Resolver\ResolverInterface;

use Ellipse\Resolvers\ActionResolverServiceProvider;
use Ellipse\Resolvers\Action;

use Ellipse\Dispatcher\Dispatcher;

use App\Controllers\SomeController;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service provider.
$container->register(ActionResolverServiceProvider::class);

// Register some base controllers namespace.
$container->set('resolvers.action.controllers_namespace', '\App\Controllers');

// Now a resolver using action resolver is available.
$resolver = $container->get(ResolverInterface::class);

// Action instances can now be dispatched using this resolver.
$dispatcher = new Dispatcher([
    new Action('SomeController@index'),
], $resolver);

// The given request is processed by SomeController's index method.
$response = $dispatcher->dispatch($request);
```

`DispatcherInterface` implementation can also be used directly:

```php
<?php

namespace App;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;

use Ellipse\Resolvers\ActionResolverServiceProvider;
use Ellipse\Resolvers\Action;

use App\Controllers\SomeController;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service provider.
$container->register(ActionResolverServiceProvider::class);

// Register some base controllers namespace.
$container->set('resolvers.action.controllers_namespace', '\App\Controllers');

// Now a dispatcher using action resolver is available.
$dispatcher = $container->get(DispatcherInterface::class)
    ->with(new Action('SomeController@index'));

// The given request is processed by SomeController's index method.
$response = $dispatcher->dispatch($request);
```

### Recursive resolver
The package [ellipse/resolvers-recursive](https://github.com/ellipsephp/resolvers-recursive)
contains a service provider named `RecursiveResolverServiceProvider` which
provides three implementations:

* one implementation of `RecursiveResolver`.
* one implementation of `ResolverInterface` with a `RecursiveResolver` added at
  the beginning of the chain.
* one implementation of `DispatcherInterface` using this resolver.

The `RecursiveResolver` class allows to register either list of elements or
`Traversable` instances containing elements.

`RecursiveResolverServiceProvider` implements [interop service provider](https://github.com/container-interop/container-interop)
so it should be used with containers that can handle them. For example [simplex](https://github.com/mnapoli/simplex).

When used without the service provider, please note `RecursiveResolver` must be
the first resolver in the chain of resolvers in order to work properly.

```php
<?php

namespace App;

use Ellipse\Contracts\Resolver\ResolverInterface;

use Ellipse\Resolvers\RecursiveResolverServiceProvider;

use Ellipse\Dispatcher\Dispatcher;

use App\Middleware\SomeMiddleware1;
use App\Middleware\SomeMiddleware2;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service provider.
$container->register(RecursiveResolverServiceProvider::class);

// Now a resolver using recursive resolver is available.
$resolver = $container->get(ResolverInterface::class);

// List of elements can now be dispatched using this resolver.
$dispatcher = (new Dispatcher([], $resolver))->with([
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// The given request is processed by SomeMiddleware1 and SomeMiddleware2.
$response = $dispatcher->dispatch($request);
```

`DispatcherInterface` implementation can also be used directly:

```php
<?php

namespace App;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;

use Ellipse\Resolvers\RecursiveResolverServiceProvider;

use App\Middleware\SomeMiddleware1;
use App\Middleware\SomeMiddleware2;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service provider.
$container->register(ResolverResolverServiceProvider::class);

// Now a dispatcher using recursive resolver is available.
$dispatcher = $container->get(DispatcherInterface::class)->with([
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// The given request is processed by SomeMiddleware1 and SomeMiddleware2.
$response = $dispatcher->dispatch($request);
```

### Using multiple resolvers
As mentioned above, the recommended way to use multiple resolvers it to chain
them using their `->withDelegate()` method :

```php
<?php

namespace App;

use Ellipse\Resolvers\CallableResolverServiceProvider;
use Ellipse\Resolvers\ContainerResolverServiceProvider;

use Ellipse\Dispatcher\Dispatcher;

use App\Middleware\SomeMiddleware;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service providers.
$container->register(CallableResolverServiceProvider::class);
$container->register(ContainerResolverServiceProvider::class);

// Now both resolvers are available.
$callable_resolver = $container->get(CallableResolver::class);
$container_resolver = $container->get(ContainerResolver::class);

// They can be chained togerther.
$resolver = $callable_resolver->withDelegate($container_resolver);

// And used with a dispatcher.
$some_callable = function ($request, $delegate) { // ... returns a response };

$container->set(SomeMiddleware::class, function () {

    // return an implementation of SomeMiddleware
    return new SomeMiddleware(...);

});

$dispatcher = new Dispatcher([
    $some_callable,
    SomeMiddleware::class,
], $resolver);

// The given request is processed by $some_callable and SomeMiddleware.
$response = $dispatcher->dispatch($request);
```

Of course this works out of the box when retrieving `ResolverInterface` or
`DispatcherInterface` from the container:

```php
<?php

namespace App;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;

use Ellipse\Resolvers\CallableResolverServiceProvider;
use Ellipse\Resolvers\ContainerResolverServiceProvider;
use Ellipse\Resolvers\RecursiveResolverServiceProvider;

use App\Middleware\SomeMiddleware;

// Some container capable of handling interop service provider.
$container = new SomeContainer;

// Register the service providers.
$container->register(CallableResolverServiceProvider::class);
$container->register(ContainerResolverServiceProvider::class);
$container->register(RecursiveResolverServiceProvider::class);

// Now a dispatcher is available using the three resolvers.
$some_callable = function ($request, $delegate) { // ... returns a response };

$container->set(SomeMiddleware::class, function () {

    // return an implementation of SomeMiddleware
    return new SomeMiddleware(...);

});

$dispatcher = $container->get(DispatcherInterface::class)->with([
    $some_callable,
    SomeMiddleware::class,
]);

// The given request is processed by $some_callable and SomeMiddleware.
$response = $dispatcher->dispatch($request);
```
