<?php declare(strict_types=1);

namespace Ellipse;

use Traversable;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

class Dispatcher implements RequestHandlerInterface
{
    /**
     * The decorated request handler.
     *
     * @var \Psr\Http\Message\RequestHandlerInterface
     */
    private $handler;

    /**
     * The iterable list of middleware wrapped around the decorated request
     * handler.
     *
     * @var iterable
     */
    private $middleware;

    /**
     * Set up a dispatcher with the given request handler and iterable list of
     * middleware.
     *
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     * @param iterable                                      $middleware
     */
    public function __construct(RequestHandlerInterface $handler, iterable $middleware = [])
    {
        $this->handler = $handler;
        $this->middleware = $middleware;
    }

    /**
     * Return a new Dispatcher with the current one wrapped inside the given
     * middleware.
     *
     * @param \Interop\Http\Server\MiddlewareInterface $middleware
     * @return \Ellipse\Dispatcher
     */
    public function with(MiddlewareInterface $middleware): Dispatcher
    {
        return new Dispatcher($this, [$middleware]);
    }

    /**
     * Handle a request by processing it with all the middleware before handling
     * it with the request handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\MiddlewareTypeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->middleware instanceof Traversable
            ? iterator_to_array($this->middleware)
            : $this->middleware;

        if (count($middleware) > 0) {

            $head = array_shift($middleware);

            if ($head instanceof MiddlewareInterface) {

                return $head->process($request, new Dispatcher($this->handler, $middleware));

            }

            throw new MiddlewareTypeException($head);

        }

        return $this->handler->handle($request);
    }
}
