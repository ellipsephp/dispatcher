<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;

class ComposableResolver implements DispatcherFactoryInterface
{
    /**
     * The delegate.
     *
     * @var \Ellipse\DispatcherFactoryInterface
     */
    private $delegate;

    /**
     * Set up a composable resolver with the given delegate.
     *
     * @param \Ellipse\DispatcherFactoryInterface $delegate
     */
    public function __construct(DispatcherFactoryInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * Returns a new DispatcherWithMiddleware using the delegate and the given
     * iterable middleware queue.
     *
     * @param iterable $middleware
     * @return \Ellipse\Dispatcher\ResolverWithMiddleware
     */
    public function with(iterable $middleware): ResolverWithMiddleware
    {
        return new ResolverWithMiddleware($this->delegate, $middleware);
    }

    /**
     * Proxy the delegate.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        return ($this->delegate)($handler, $middleware);
    }
}
