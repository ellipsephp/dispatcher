<?php declare(strict_types=1);

namespace Ellipse;

use Traversable;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Handlers\RequestHandlerWithMiddleware;
use Ellipse\Handlers\RequestHandlerWithMiddlewareQueue;
use Ellipse\Handlers\Exceptions\MiddlewareTypeException as BaseMiddlewareTypeException;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

class Dispatcher extends RequestHandlerWithMiddlewareQueue
{
    /**
     * Set up a dispatcher with the given request handler to decorate and the
     * iterable middleware queue wrapping it.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface  $handler
     * @param iterable                                  $middleware
     * @throws \Ellipse\Dispatcher\Exceptions\MiddlewareTypeException
     */
    public function __construct(RequestHandlerInterface $handler, iterable $middleware = [])
    {
        try {

            parent::__construct($handler, $middleware instanceof Traversable
                ? iterator_to_array($middleware)
                : $middleware);

        }

        catch (BaseMiddlewareTypeException $e) {

            throw new MiddlewareTypeException($e->value());

        }
    }

    /**
     * Return a new Dispatcher with the given middleware wrapped around the
     * current one.
     *
     * @param \Psr\Http\Server\MiddlewareInterface $middleware
     * @return \Ellipse\Dispatcher
     */
    public function with(MiddlewareInterface $middleware): Dispatcher
    {
        return new Dispatcher(new RequestHandlerWithMiddleware($this, $middleware));
    }
}
