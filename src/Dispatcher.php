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
     * The iterable list of middleware to dispatch.
     *
     * @var iterable
     */
    private $middleware;

    /**
     * The final request handler.
     *
     * @var \Psr\Http\Message\RequestHandlerInterface
     */
    private $handler;

    /**
     * Sets up a dispatcher with the given iterable list of middleware and
     * request handler.
     *
     * @param iterable                                      $middleware
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     */
    public function __construct(iterable $middleware, RequestHandlerInterface $handler)
    {
        $this->middleware = $middleware;
        $this->handler = $handler;
    }

    /**
     * Handle a request by processing it through all the middleware before
     * handling it with the final request handler.
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

            $first = array_shift($middleware);

            if ($first instanceof MiddlewareInterface) {

                return $first->process($request, new Dispatcher($middleware, $this->handler));

            }

            throw new MiddlewareTypeException($first);

        }

        return $this->handler->handle($request);
    }
}
