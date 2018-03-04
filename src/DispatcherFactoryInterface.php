<?php declare(strict_types=1);

namespace Ellipse;

interface DispatcherFactoryInterface
{
    /**
     * Return a new Dispatcher using the given request handler and middleware
     * queue.
     *
     * @param mixed $handler
     * @param array $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, array $middleware = []): Dispatcher;
}
