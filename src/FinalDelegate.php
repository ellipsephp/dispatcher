<?php

namespace Pmall\Stack;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;

use Pmall\Contracts\Stack\Exceptions\NoResponseReturnedException;

class FinalDelegate implements DelegateInterface
{
    /**
     * Implements psr 15 middleware convention.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request the given request.
     * @return void
     * @throws \Pmall\Dispatcher\Exceptions\NoResponseReturnedException
     */
    public function process(ServerRequestInterface $request)
    {
        throw $this($request);
    }

    /**
     * Throw an exception because a response must be returned before reaching
     * this delegate.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request the given request.
     * @return void
     * @throws \Pmall\Contracts\Stack\Exceptions\NoResponseReturnedException
     */
    public function __invoke(ServerRequestInterface $request)
    {
        throw new NoResponseReturnedException;
    }
}
