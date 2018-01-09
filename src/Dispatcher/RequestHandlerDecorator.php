<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

class RequestHandlerDecorator implements RequestHandlerInterface
{
    /**
     * The middleware to process.
     *
     * @param \Interop\Http\Server\MiddlewareInterface
     */
    private $middleware;

    /**
     * The handler to give to the middleware.
     *
     * @param \Interop\Http\Server\RequestHandlerInterface
     */
    private $handler;

    /**
     * Set up a request handler decorator with the given middleware and request
     * handler.
     *
     * @param \Interop\Http\Server\MiddlewareInterface
     * @param \Interop\Http\Server\RequestHandlerInterface
     */
    public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $handler)
    {
        $this->middleware = $middleware;
        $this->handler = $handler;
    }

    /**
     * Proxy the middleware ->process() method with the given request and the
     * request handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->handler);
    }
}
