# Middleware stack
This package is a **Psr-15 middleware dispatcher** requiring **php 7.1**.

The terms used in the following documentation:
* *middleware* represents objects implementing [Psr-15](https://github.com/http-interop/http-middleware) `MiddlewareInterface`.
* *delegate* represents objects implementing [Psr-15](https://github.com/http-interop/http-middleware) `DelegateInterface`.
* *request* represents objects implementing [Psr-7](https://github.com/php-fig/http-message) `ServerRequestInterface`.
* *response* represents objects implementing [Psr-7](https://github.com/php-fig/http-message) `ResponseInterface`.
* *element* represents any value.

This package provides a `MiddlewareStack` class which can be used to dispatch a
request returning the response produced by a list of middleware composing the
stack. See [getting started](#getting-started).

`MiddlewareStack` instances can be composed of middleware as well as any element
as long as it can be resolved as a middleware by the resolver. See
[pushing middleware](#pushing-middleware) and
[pushing elements](#pushing-elements).

`MiddlewareStack` instances can use a custom resolver for resolving elements
as middleware. Ellipse packages provide some useful resolvers but any object can
be used as a resolver as long as it implements the
`Ellipse\Contracts\Resolver\ResolverInterface` from the
`ellipse/contracts-resolver` package. Also, many resolvers can be aggregated by
one using the `ResolverAggregate` class from the `ellipse/resolvers-aggregate`
package. See [resolvers](#resolvers).

Please note that Psr-15 specification is not yet official and may be subject to
changes.

**Require** php >= 7.1

**Installation** `composer require ellipse/stack`

**Run tests** `./vendor/bin/peridot tests`

* [Getting started](#getting-started)
* [Pushing middleware](#pushing-middleware)
* [Pushing elements](#pushing-elements)
* [Resolvers](#resolvers)
    * [Resolver interface](#resolver-interface)
    * [Abstract resolver](#abstract-resolver)
    * [Callable resolver](#callable-resolver)
    * [Container resolver](#container-resolver)
    * [Action resolver](#action-resolver)
    * [Recursive resolver](#recursive-resolver)
    * [Resolver aggregate](#resolver-aggregate)
    * [Default resolver](#default-resolver)

## Getting started
A middleware stack can be instantiated with an optional resolver and an optional
list of elements to push into the stack. The elements list can either be an
array or a `Traversable` instance. An element can be either a middleware or
anything that can be resolved as a middleware by the given resolver.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;

// Can be instantiated without anything.
$stack = new MiddlewareStack;

// Can be instantiated with an object implementing ResolverInterface.
$stack = new MiddlewareStack($resolver);

// A list of middleware can also be given on instantiation.
$stack = new MiddlewareStack($resolver, [
    $middleware1,
    $middleware2,
    $middleware3,
]);

// The list of middleware can be a Traversable instance containing the list of
// middleware.
$stack = new MiddlewareStack($resolver, new \ArrayObject([
    $middleware1,
    $middleware2,
    $middleware3,
]));
```

Once the `MiddlewareStack` is built it can dispatch requests and return the
response produced by the list of middleware composing the stack.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;

$stack = new MiddlewareStack($resolver, [
    $middleware1,
    $middleware2,
    $middleware3,
]);

// Get the response produced by $middleware1, 2, and 3 for the given request.
$response = $stack->dispatch($request);
```

Please also note `MiddlewareStack` class implements Psr-15 `MiddlewareInterface`
so instances of `MiddlewareStack` can be pushed into another instance of
`MiddlewareStack`, or into any other Psr-15 middleware dispatcher.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;

// This is a Psr-15 middleware.
$stack1 = new MiddlewareStack($resolver, [
    $middleware1,
    $middleware2,
    $middleware3,
]);

// It can be pushed into another middleware dispatcher.
$stack2 = new MiddlewareStack($resolver, [
    $stack1,
    $middleware4,
    $middleware5
]);

// Get the response produced by $middleware1, 2, 3, 4 and 5 for the given
// request.
$response = $stack2->dispatch($request);
```

## Pushing middleware
Middleware are pushed into the stack using the `withMiddleware` method. No
resolver is needed to use middleware.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;

$stack = (new MiddlewareStack)->withMiddleware($middleware);
```

Note `MiddlewareStack` instances are immutable, so calling `withMiddleware`
method returns a new `MiddlewareStack` instance leaving the calling
`MiddlewareStack` instance unmodified.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;

$stack1 = new MiddlewareStack($resolver, [
    $middleware1,
    $middleware2,
]);

$stack2 = $stack1->withMiddleware($middleware3);

// Get the response produced by $middleware1, and 2 for the given request.
$response = $stack1->dispatch($request);

// Get the response produced by $middleware1, 2, and 3 for the given request.
$response = $stack2->dispatch($request);
```

## Pushing elements
Anything can be pushed in `MiddlewareStack` instances as long as the resolver
can resolve it as a middleware. If the resolver is not able to resolve an
element, an `ElementCantBeResolvedException` is thrown.

When no resolver is specified on `MiddlewareStack` instantiation, a
`DefaultResolver` instance is used by default. This resolver can resolve
callable elements and list of callable elements. See
[default resolver](#default-resolver).

Elements are pushed into the stack using the `withElement` method :

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;

$stack = (new MiddlewareStack($resolver))->withElement($element);
```

Note `MiddlewareStack` instances are immutable, so calling `withElement`
method returns a new `MiddlewareStack` instance leaving the calling
`MiddlewareStack` instance unmodified.

Also note pushing middleware using the `withElement` method is the same as using
the `withMiddleware` method.

## Resolvers
* [Resolver interface](#resolver-interface)
* [Abstract resolver](#abstract-resolver)
* [Callable resolver](#callable-resolver)
* [Container resolver](#container-resolver)
* [Action resolver](#action-resolver)
* [Recursive resolver](#recursive-resolver)
* [Resolver aggregate](#resolver-aggregate)
* [Default resolver](#default-resolver)

### Resolver interface
A resolver usable by `MiddlewareStack` instances is any object implementing
`Ellipse\Contracts\Resolver\ResolverInterface` from the
`ellipse/contracts-resolver` package.

The only method defined by `ResolverInterface` is the `resolve` method, taking
one value of any type as parameter, and returning a middleware. When the element
can't be resolved,
`Ellipse\Contracts\Resolver\Exception\ElementCantBeResolvedException` must be
thrown.

```php
<?php

namespace App\Resolvers;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

use Ellipse\Contracts\Resolver\ResolverInterface;
use Ellipse\Contracts\Resolver\Exceptions\ElementCantBeResolvedException;

class MyResolver implements ResolverInterface
{
    public function resolver($element): MiddlewareInterface
    {
        if ($this->canResolve($element)) {

            return $this->getMiddleware($element);

        }

        throw new ElementCantBeResolvedException($element);
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

* In order to abstract this process, an `AbstractResolver` class is available from
  the `ellipse/resolvers-abstract` package. See
  [abstract resolver](#abstract-resolver).

* The recommended way of resolving many element types is to create one resolver
  for each single type of element and then to aggregate those resolvers using
  `ResolverAggregate`. See [resolver aggregate](#resolver-aggregate).

* When no resolver is specified on `MiddlewareStack` instantiation, an instance of
  `DefaultResolver` is used by default. See [default resolver](#default-resolver).

* Some useful resolvers are provided by Ellipse packages, see
  [callable resolver](#callable-resolver),
  [container resolver](#container-resolver), [action resolver](#action-resolver)
  and [recursive resolver](#recursive-resolver).

### Abstract resolver
To ease the creation of resolver classes, an `AbstractResolver` class is
provided by the `ellipse/resolvers-abstract` package. Two method must be defined
when extending `AbstractResolver`, the `canResolve` method and the
`getMiddleware` method.

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
Any callable value can be resolved as middleware using the `CallableResolver`
class provided by the `ellpise/resolver-callable` package. In order to work
properly those callable values must take a $request and a $delegate as parameter
and return a response.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;
use Ellipse\Resolvers\CallableResolver;

// Create a callable behaving like a middleware.
$some_callable = function ($request, $delegate) {

    // ... returns a response

};

$resolver = new CallableResolver;

// Create a stack using this resolver and containing the callable.
$stack = new MiddlewareStack($resolver, [
    $some_callable
]);

// The given request is processed by $some_callable.
$response = $stack->dispatch($request);
```

### Container resolver
Sometimes it is useful to retrieve middleware from the application container
instead of pushing an actual instance of the middleware. This can easily be
achieved using the `ContainerResolver` class from the
`ellipse/resolvers-container` package.

This resolver expects the container to implements `ContainerInterface` from the
[container interop specification](https://github.com/container-interop/container-interop).
Also it provides a service provider implementing the
[service provider interop specification](https://github.com/container-interop/service-provider),
so your application container should be able to deal with it.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;
use Ellipse\Resolvers\ContainerResolverServiceProvider;
use Ellipse\Resolvers\ContainerResolver;

// Some middleware instance.
use App\Middleware\SomeMiddleware;

// Some container implementing interop's ContainerInterface and able to handle
// interop's ServiceProvider.
$container = new SomeContainer;

// Add the service provider to the container.
$container->addServiceProvider(new ContainerResolverServiceProvider);

// Register some middleware into the container.
$container->share(SomeMiddleware::class);

// Get a container resolver instance from the container.
$resolver = $container->get(ContainerResolver::class);

// Create a stack using this resolver and containing the middleware class name
// in the elements list.
$stack = new MiddlewareStack($resolver, [
    SomeMiddleware::class,
]);

// The given request is processed by an instance of SomeMiddleware retrieved
// from the container.
$response = $stack->dispatch($request);
```

### Action resolver
The `ActionResolver` class from the `ellipse/resolvers-action` provides an easy
way to use actions as middleware. Actions are strings formatted this way:
`'ClassName@MethodName'`. Many things to note:

* Action strings must contain the class name and the method to use separated by
  @. For example `UsersController@index` will retrieve an instance of
  `UsersController` from the container and will call its index method through
  the container as well.
* This resolver expects the same kind of container as `ContainerResolver`.
  However right now it also expects the container to have a `call` method
  allowing to call class methods through the container. This is subject to be
  simplified in future versions.
* Actions are expected to return a response as no delegate will be passed to
  the class method.
* If a `'resolvers.action.controllers_namespace'` alias is registered in the
  container, its value will be prepended to all action's classes name.
* The produced action middleware register the request into the container when it
  process the request before calling the action, so it can be injected in the
  action's class constructor or in the action's method.
* Request attributes are used when calling the class method through the
  container. Actions takes their full power when used with the Ellipse router
  which will (hopefully) be released soon. This will allow to pass matched url
  values to the action.

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
        $this->some_service = $some_service;
        $this->response_factory = $response_factory
    }

    public function index(ServerRequestInterface $request)
    {
        // some processing

        // ...

        return $this->response_factory->createResponse();
    }
}
```

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;
use Ellipse\Resolvers\ActionResolverServiceProvider;
use Ellipse\Resolvers\ActionResolver;

use App\Controllers\SomeController;

// Some container implementing interop's ContainerInterface and able to handle
// interop's ServiceProvider.
$container = new SomeContainer;

// Add the service provider to the container.
$container->addServiceProvider(new ActionResolverServiceProvider);

// Register some controller class into the container.
$container->share(SomeController::class);

// Register the controllers namespace.
$container->share('resolvers.action.controllers_namespace', 'App\Controllers');

// Get an action resolver instance from the container.
$resolver = $container->get(ActionResolver::class);

// Create a stack using this resolver and containing an action string.
$stack = new MiddlewareStack($resolver, [
    'SomeController@index',
]);

// The given request is processed by the index method of the SomeMiddleware
// class.
$response = $stack->dispatch($request);
```

### Recursive resolver
The `RecursiveResolver` class from the `ellipse/resolvers-recursive` package
allows to resolve array of elements or `Traversable` instances containing
elements added to the stack with the `withElement` method. It takes a resolver
as parameter which is used to resolve elements in the list.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;
use Ellipse\Resolvers\RecursiveResolver;
use Ellipse\Resolvers\CallableResolver;

$callable1 = function ($request, $delegate) {

    // ... returns a response

};

$callable2 = function ($request, $delegate) {

    // ... returns a response

};

// Create the recursive resolver wrapped around a callable resolver.
$resolver = new RecursiveResolver(new CallableResolver);

// Create a stack using this resolver.
$stack = new MiddlewareStack($resolver);

// Array of elements or Traversable instance of elements can be pushed into the
// stack.
$stack = $stack->withElement([
    $callable1,
    $callable2,
]);

// The given request is processed by callable1 and 2.
$response = $stack->dispatch($request);
```

### Resolver aggregate
The `ResolverAggregate` class from the `ellipse/resolvers-aggregate` package
allows to use many resolvers at once. It takes this list of resolvers or a
`Traversable` instance containing the resolvers on instantiation.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;
use Ellipse\Resolvers\ContainerResolverServiceProvider;
use Ellipse\Resolvers\ResolverAggregate;
use Ellipse\Resolvers\CallableResolver;
use Ellipse\Resolvers\ContainerResolver;

use App\Middleware\SomeMiddleware;

$callable = function ($request, $delegate) {

    // ... returns a response

};

$container = new SomeContainer;

$container->addServiceProvider(new ContainerResolverServiceProvider);

$container->share(SomeMiddleware::class);

// Create a CallableResolver and a ContainerResolver
$callable_resolver = new CallableResolver;
$container_resolver = $container->get(ContainerResolver::class);

// Create the resolver aggregate, aggregating the callable resolver and
// the container resolver.
$resolver = new ResolverAggregate([
    $callable_resolver,
    $container_resolver,
]);

// Create a stack using this resolver and containing a callable and a middleware
// class name.
$stack = new MiddlewareStack($resolver, [
    $callable,
    SomeMiddleware::class,
]);

// The given request is processed by $callable and SomeMiddleware.
$response = $stack->dispatch($request);
```

### Default resolver
When no resolver is specified on middleware stack instanciation, a
`Defaultresolver` instance is used. It is just a `RecursiveResolver` wrapping
a `CallableResolver`.

```php
<?php

namespace App;

use Ellipse\Stack\MiddlewareStack;

$callable1 = function ($request, $delegate) {

    // ... returns a response

};

$callable2 = function ($request, $delegate) {

    // ... returns a response

};

// Create a stack with no resolver.
$stack = new MiddlewareStack;

// This works by default.
$response = $stack->withElement([$callable1, $callable2])->dispatch($request);
```
