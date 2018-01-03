<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Ellipse\DispatcherFactoryInterface;

interface ComposableResolverInterface extends DispatcherFactoryInterface
{
    /**
     * Return a new ResolverWithMiddleware using this resolver as delegate and
     * the given iterable list of middleware.
     *
     * @param iterable $middleware
     * @return \Ellipse\Dispatcher\ResolverWithMiddleware
     */
    public function with(iterable $middleware): ResolverWithMiddleware;
}
