<?php declare(strict_types=1);

namespace Ellipse;

use Ellipse\Dispatcher\MiddlewareStack;
use Ellipse\Dispatcher\RequestHandlerProxy;

class DispatcherFactory
{
    /**
     * The middleware resolver.
     *
     * @var callable
     */
    private $middleware;

    /**
     * The handler resolver.
     *
     * @var callable
     */
    private $handler;

    /**
     * Set up a dispatcher factory with optional middleware and handler
     * resolvers.
     *
     * @param callable $middleware
     * @param callable $handler
     */
    public function __construct(callable $middleware = null, callable $handler = null)
    {
        $this->middleware = $middleware;
        $this->handler = $handler;
    }

    /**
     * Return a new dispatcher from the given middleware stack and handler.
     *
     * @param iterable  $middleware
     * @param mixed     $handler
     * @return \Ellipse\Dispatcher
     */
    public function __invoke(iterable $middleware, $handler): Dispatcher
    {
        $middleware = new MiddlewareStack($middleware, $this->middleware);
        $handler = new RequestHandlerProxy($handler, $this->handler);

        return new Dispatcher($middleware, $handler);
    }
}
