<?php

namespace Pmall\Stack;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

class CallableWrapper implements MiddlewareInterface
{
    /**
     * Tha callable middleware.
     *
     * @var callable
     */
    private $cb;

    /**
     * Set up a callable wrapper with the callable middleware.
     *
     * @param callable $cb the callable middleware.
     */
    public function __construct(callable $cb)
    {
        $this->cb = $cb;
    }

    /**
     * Implements psr 15 middleware convention.
     *
     * @param \Psr\Http\Message\ServerRequestInterface          $request    the incoming request.
     * @param \Interop\Http\ServerMiddleware\DelegateInterface  $delegate   the next middleware to execute.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this($request, $delegate);
    }

    /**
    * Process the request with the callable middleware.
    *
    * @param \Psr\Http\Message\ServerRequestInterface           $request    the incoming request.
    * @param \Interop\Http\ServerMiddleware\DelegateInterface   $delegate   the next middleware to execute.
    * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return ($this->cb)($request, $delegate);
    }
}
