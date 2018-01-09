<?php declare(strict_types=1);

namespace Ellipse;

use Traversable;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerWithMiddleware;
use Ellipse\Dispatcher\RequestHandlerWithMiddlewareQueue;

class Dispatcher extends RequestHandlerWithMiddlewareQueue
{
    /**
     * Set up a dispatcher with the given request handler to decorate and the
     * iterable middleware queue wrapping it.
     *
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     * @param iterable                                      $middleware
     */
    public function __construct(RequestHandlerInterface $handler, iterable $middleware = [])
    {
        parent::__construct($handler, $middleware instanceof Traversable
            ? iterator_to_array($middleware)
            : $middleware);
    }

    /**
     * Return a new Dispatcher with the given middleware wrapped around the
     * current one.
     *
     * @param \Interop\Http\Server\MiddlewareInterface $middleware
     * @return \Ellipse\Dispatcher
     */
    public function with(MiddlewareInterface $middleware): Dispatcher
    {
        return new Dispatcher(new RequestHandlerWithMiddleware($this, $middleware));
    }
}
