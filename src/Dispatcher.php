<?php declare(strict_types=1);

namespace Ellipse;

use Traversable;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerWithMiddleware;
use Ellipse\Dispatcher\RequestHandlerWithMiddlewareQueue;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

class Dispatcher extends RequestHandlerWithMiddlewareQueue
{
    /**
     * Set up a dispatcher with the given request handler to decorate and the
     * iterable middleware queue wrapping it.
     *
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     * @param iterable                                      $middleware
     * @throws \Ellipse\Dispatcher\Exceptions\MiddlewareTypeException
     */
    public function __construct(RequestHandlerInterface $handler, iterable $middleware = [])
    {
        try {

            parent::__construct($handler, $middleware instanceof Traversable
                ? iterator_to_array($middleware)
                : $middleware);

        }

        catch (MiddlewareTypeException $e) {

            throw $e;

        }
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
