<?php declare(strict_types=1);

namespace Ellipse\Stack;

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
     * @param \Interop\Http\ServerMiddleware\DelegateInterface $delegate
     */
    public function __construct(DelegateInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * Return the response of the delegate injected from outside the stack.
     *
     * The injected delegate which delegates the process to the next middleware
     * of the parent stack is used instead of the delegate passed to this
     * method, which would fail as it is the final delegate of the current
     * stack.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\DelegateInterface       $delegate
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // the injected delegate should be used instead of the current delegate
        // which is the final delegate of the stack.
        return $this->delegate->process($request);
    }
}
