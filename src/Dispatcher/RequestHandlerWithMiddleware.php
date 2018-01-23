<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerWithMiddleware implements RequestHandlerInterface
{
    /**
     * The delegate.
     *
     * @var \Psr\Http\Server\RequestHandlerInterface
     */
    private $delegate;

    /**
     * The middleware to process.
     *
     * @var \Psr\Http\Server\MiddlewareInterface
     */
    private $middleware;

    /**
     * Set up a request handler with middleware with the given delegate and
     * middleware.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface  $delegate
     * @param \Psr\Http\Server\MiddlewareInterface      $middleware
     */
    public function __construct(RequestHandlerInterface $delegate, MiddlewareInterface $middleware)
    {
        $this->delegate = $delegate;
        $this->middleware = $middleware;
    }

    /**
     * Proxy the middleware ->process() method with the given request and the
     * delegate.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->delegate);
    }
}
