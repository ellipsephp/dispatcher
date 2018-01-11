<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;

class ResolverWithMiddleware implements DispatcherFactoryInterface
{
    /**
     * The delegate.
     *
     * @var \Ellipse\Dispatcher\DispatcherFactoryInterface
     */
    private $delegate;

    /**
     * The iterable middleware queue to pass to the delegate.
     *
     * @var iterable
     */
    private $middleware;

    /**
     * Set up a resolver with middleware with the given delegate and iterable
     * middleware queue.
     *
     * @param \\Ellipse\Dispatcher\DispatcherFactoryInterface   $delegate
     * @param iterable                                          $middleware
     */
    public function __construct(DispatcherFactoryInterface $delegate, iterable $middleware)
    {
        $this->delegate = $delegate;
        $this->middleware = $middleware;
    }

    /**
     * Returns a new DispatcherWithMiddleware using this resolver as delegate
     * and the given iterable middleware queue.
     *
     * @param iterable $middleware
     * @return \Ellipse\Dispatcher\ResolverWithMiddleware
     */
    public function with(iterable $middleware): ResolverWithMiddleware
    {
        return new ResolverWithMiddleware($this, $middleware);
    }

    /**
     * Proxy the delegate with the resolved value of the dispatcher to decorate
     * and the iterable middleware queue.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        $handler = $this->dispatcher($handler, $middleware);

        return ($this->delegate)($handler, $this->middleware);
    }

    /**
     * Proxy the first delegate which is not a ResolverWithMiddleware.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     */
    public function dispatcher($handler, iterable $middleware = []): Dispatcher
    {
        if ($this->delegate instanceof ResolverWithMiddleware) {

            return $this->delegate->dispatcher($handler, $middleware);

        }

        return ($this->delegate)($handler, $middleware);
    }
}
