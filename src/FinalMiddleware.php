<?php

namespace Pmall\Stack;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

class FinalMiddleware implements MiddlewareInterface
{
    /**
     * The delgate from outside of the stack.
     *
     * @var \Interop\Http\ServerMiddleware\DelegateInterface
     */
    private $delegate;

    /**
     * Set up a final middleware with the delegate from ouside the stack.
     *
     * @param \Interop\Http\ServerMiddleware\DelegateInterface $delegate the delegate from outside the stack.
     */
    public function __construct(DelegateInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * Implements psr 15 middleware convention.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request    the incoming request.
     * @param \Psr\Http\Message\DelegateInterface       $delegate   the next middleware to execute.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this($request, $delegate);
    }

    /**
    * Return the response of the delegate from outside the stack.
    *
    * @param \Psr\Http\Message\ServerRequestInterface  $request    the incoming request.
    * @param \Psr\Http\Message\DelegateInterface       $final      the final delegate. Obviously no use here.
    * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, DelegateInterface $final)
    {
        return $this->delegate->process($request);
    }
}
