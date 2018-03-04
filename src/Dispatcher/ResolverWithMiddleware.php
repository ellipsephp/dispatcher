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
     * The middleware queue to pass to the delegate.
     *
     * @var array
     */
    private $middleware;

    /**
     * Set up a resolver with middleware with the given delegate and middleware
     * queue.
     *
     * @param \Ellipse\Dispatcher\DispatcherFactoryInterface    $delegate
     * @param array                                             $middleware
     */
    public function __construct(DispatcherFactoryInterface $delegate, array $middleware)
    {
        $this->delegate = $delegate;
        $this->middleware = $middleware;
    }

    /**
     * Returns a new DispatcherWithMiddleware using this resolver as delegate
     * and the given middleware queue.
     *
     * @param array $middleware
     * @return \Ellipse\Dispatcher\ResolverWithMiddleware
     */
    public function with(array $middleware): ResolverWithMiddleware
    {
        return new ResolverWithMiddleware($this, $middleware);
    }

    /**
     * Proxy the delegate with the resolved value of the dispatcher to decorate
     * and the given middleware queue.
     *
     * @param mixed $handler
     * @param array $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, array $middleware = []): Dispatcher
    {
        $handler = $this->dispatcher($handler, $middleware);

        return ($this->delegate)($handler, $this->middleware);
    }

    /**
     * Proxy the first delegate which is not a ResolverWithMiddleware.
     *
     * @param mixed $handler
     * @param array $middleware
     * @return \Ellipse\Dispatcher
     */
    public function dispatcher($handler, array $middleware): Dispatcher
    {
        if ($this->delegate instanceof ResolverWithMiddleware) {

            return $this->delegate->dispatcher($handler, $middleware);

        }

        return ($this->delegate)($handler, $middleware);
    }
}
