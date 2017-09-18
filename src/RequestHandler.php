<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * The middleware to process.
     *
     * @var \Interop\Http\Server\MiddlewareInterface
     */
    private $middleware;

    /**
     * The request handler to pass to the middleware.
     *
     * @var \Interop\Http\Server\RequestHandlerInterface
     */
    private $handler;

    /**
     * Set up the handler with the middleware to process and the request handler
     * to pass to the middleware.
     *
     * @param \Interop\Http\Server\MiddlewareInterface      $middleware
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     */
    public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $handler)
    {
        $this->middleware = $middleware;
        $this->handler = $handler;
    }

    /**
     * Process the middleware with the given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException
     */
    public function handle(ServerRequestInterface $request)
    {
        $response = $this->middleware->process($request, $this->handler);

        if (! $response instanceof ResponseInterface) {

            throw new InvalidMiddlewareReturnValueException($this->middleware, $response);

        }

        return $response;
    }
}
