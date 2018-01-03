<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use IteratorAggregate;

use Interop\Http\Server\MiddlewareInterface;

use Ellipse\Dispatcher\Exceptions\MiddlewareResolvingException;

class MiddlewareProxy implements IteratorAggregate
{
    /**
     * The iterable list of middleware.
     *
     * @var iterable
     */
    private $middleware;

    /**
     * Set up a middleware proxy with the given iterable list of middleware.
     *
     * @param iterable $middleware
     */
    public function __construct(iterable $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * This is a generator proxying the iterable list of middleware by ensuring
     * they all implement MiddlewareInterface.
     *
     * @throws \Ellipse\Dispatcher\Exceptions\MiddlewareResolvingException
     */
    public function getIterator()
    {
        foreach ($this->middleware as $middleware) {

            if (! $middleware instanceof MiddlewareInterface) {

                throw new MiddlewareResolvingException($middleware);

            }

            yield $middleware;

        }
    }
}
