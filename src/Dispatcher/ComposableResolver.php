<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;

class ComposableResolver implements ComposableResolverInterface
{
    /**
     * The factory to use for resolving unresolved dispatchers.
     *
     * @var \Ellipse\DispatcherFactoryInterface
     */
    private $factory;

    /**
     * Set up a composer resolver with the given dispatcher factory.
     *
     * @param \Ellipse\DispatcherFactoryInterface $factory
     */
    public function __construct(DispatcherFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function with(iterable $middleware): ResolverWithMiddleware
    {
        return new ResolverWithMiddleware($this, $middleware);
    }

    /**
     * Proxy the ->value() method of a new UnresolvedDispatcher using the given
     * request handler and iterable list of middleware.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        return (new UnresolvedDispatcher($middleware, $handler))->value($this->factory);
    }
}
