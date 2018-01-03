<?php declare(strict_types=1);

namespace Ellipse;

use Ellipse\Dispatcher\MiddlewareProxy;
use Ellipse\Dispatcher\RequestHandlerProxy;

class DispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * Return a new Dispatcher by wrapping the given request handler and
     * iterable list of middleware into proxies.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        $middleware = new MiddlewareProxy($middleware);
        $handler = new RequestHandlerProxy($handler);

        return new Dispatcher($middleware, $handler);
    }
}
