<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException;

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
     * @param \Interop\Http\ServerMiddleware\MiddlewareInterface    $middleware
     * @param \Interop\Http\ServerMiddleware\DelegateInterface      $delegate
     */
    public function __construct(MiddlewareInterface $middleware, DelegateInterface $delegate)
    {
        $this->middleware = $middleware;
        $this->delegate = $delegate;
    }

    /**
     * Process the middleware with the given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException
     */
    public function process(ServerRequestInterface $request)
    {
        $response = $this->middleware->process($request, $this->delegate);

        if (! $response instanceof ResponseInterface) {

            throw new InvalidMiddlewareReturnValueException($this->middleware, $response);

        }

        return $response;
    }
}
