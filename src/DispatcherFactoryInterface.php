<?php declare(strict_types=1);

namespace Ellipse;

interface DispatcherFactoryInterface
{
    /**
     * Return a new Dispatcher using the given request handler and iterable list
     * of middleware.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher;
}
