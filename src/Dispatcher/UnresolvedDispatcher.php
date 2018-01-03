<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;

class UnresolvedDispatcher
{
    /**
     * The iterable list of middleware to dispatch.
     *
     * @var iterable
     */
    private $middleware;

    /**
     * The final request handler.
     *
     * @var mixed
     */
    private $handler;

    /**
     * Set up an unresolved dispatcher with the given iterable list of
     * middleware and request handler.
     *
     * @param iterable  $middleware
     * @param mixed     $handler
     */
    public function __construct(iterable $middleware, $handler)
    {
        $this->middleware = $middleware;
        $this->handler = $handler;
    }

    /**
     * Proxy the given factory with the request handler and the iterable list of
     * middleware. Use the given factory to resolve the value of the given
     * request handler when it is an UnresolvedDispatcher.
     *
     * @param \Ellipse\DispatcherFactoryInterface $factory
     * @return \Ellipse\Dispatcher
     */
    public function value(DispatcherFactoryInterface $factory): Dispatcher
    {
        $handler = $this->handler instanceof UnresolvedDispatcher
            ? $this->handler->value($factory)
            : $this->handler;

        return $factory($handler, $this->middleware);
    }
}
