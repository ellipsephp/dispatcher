<?php

namespace Pmall\Stack;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Pmall\Contracts\Stack\Exceptions\InvalidMiddlewareReturnValueException;

class Delegate implements DelegateInterface
{
    /**
     * The middleware to process.
     *
     * @var \Interop\Http\ServerMiddleware\MiddlewareInterface
     */
    private $middleware;

    /**
     * The delegate to pass to the middleware.
     *
     * @var \Interop\Http\ServerMiddleware\DelegateInterface
     */
    private $delegate;

    /**
     * Set up the delegate with the middleware to process and the delegate to
     * pass to the middleware.
     *
     * @param \Interop\Http\ServerMiddleware\MiddlewareInterface    $middleware the middleware to process.
     * @param \Interop\Http\ServerMiddleware\DelegateInterface      $delegate   the delegate to pass to the middleware.
     */
    public function __construct(MiddlewareInterface $middleware, DelegateInterface $delegate)
    {
        $this->middleware = $middleware;
        $this->delegate = $delegate;
    }

    /**
     * Implements psr 15 middleware convention.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request the request to process.
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Pmall\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException
     */
    public function process(ServerRequestInterface $request)
    {
        return $this($request);
    }

    /**
     * Process the middleware with the given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request the request to process.
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Pmall\Contracts\Stack\Exceptions\InvalidMiddlewareReturnValueException
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $response = $this->middleware->process($request, $this->delegate);

        if (! $response instanceof ResponseInterface) {

            throw new InvalidMiddlewareReturnValueException($middleware, $response);

        }

        return $response;
    }
}
