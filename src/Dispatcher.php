<?php declare(strict_types=1);

namespace Ellipse;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Handlers\RequestHandlerWithMiddleware;
use Ellipse\Handlers\RequestHandlerWithMiddlewareQueue;
use Ellipse\Handlers\Exceptions\MiddlewareTypeException as BaseMiddlewareTypeException;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;
use Ellipse\Dispatcher\Exceptions\MiddlewareQueueTypeException;

class Dispatcher extends RequestHandlerWithMiddlewareQueue
{
    /**
     * Set up a dispatcher with the given middleware queue wrapped around the
     * given request handler.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface  $handler
     * @param array                                     $middleware
     * @throws \Ellipse\Dispatcher\Exceptions\MiddlewareTypeException
     */
    public function __construct(RequestHandlerInterface $handler, array $middleware = [])
    {
        try {

            parent::__construct($handler, $middleware);

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
        return new Dispatcher($this, [$middleware]);
    }
}
