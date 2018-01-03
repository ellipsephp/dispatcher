<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Ellipse\Dispatcher;

class ResolverWithMiddleware implements ComposableResolverInterface
{
    /**
     * The delegate.
     *
     * @var \Ellipse\Dispatcher\ComposableResolverInterface
     */
    private $delegate;

    /**
     * The iterable list of middleware to pass to the delegate.
     *
     * @var iterable
     */
    private $middleware;

    /**
     * Set up a resolver with middleware with the given delegate and iterable
     * list of middleware.
     *
     * @param \Ellipse\Dispatcher\ComposableResolverInterface   $delegate
     * @param iterable                                          $middleware
     */
    public function __construct(ComposableResolverInterface $delegate, iterable $middleware)
    {
        $this->delegate = $delegate;
        $this->middleware = $middleware;
    }

    /**
     * @inheritdoc
     */
    public function with(iterable $middleware): ResolverWithMiddleware
    {
        return new ResolverWithMiddleware($this, $middleware);
    }

    /**
     * Proxy the delegate with a new UnresolvedDispatcher and the iterable list
     * of middleware.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        $handler = new UnresolvedDispatcher($middleware, $handler);

        return ($this->delegate)($handler, $this->middleware);
    }
}
