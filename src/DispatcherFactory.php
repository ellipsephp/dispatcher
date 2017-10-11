<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

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
     * Return a new dispatcher from the given middleware stack and handler,
     * using the middleware and request handler resolvers.
     *
     * @param mixed $middleware
     * @param mixed $handler
     * @return \Ellipse\Dispatcher\Dispatcher
     */
    public function __invoke($middleware, $handler)
    {
        $middleware = new MiddlewareStack($middleware, $this->middleware);
        $handler = new RequestHandlerProxy($handler, $this->handler);

        return new Dispatcher($middleware, $handler);
    }
}
