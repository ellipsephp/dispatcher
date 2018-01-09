<?php declare(strict_types=1);

namespace Ellipse;

use TypeError;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\DispatcherCreationException;

class DispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * Return a new Dispatcher using the given request handler and iterable
     * middleware queue. Catch any middleware or request handler type error and
     * rethrow them wrapped around a dispatcher creation exception.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     * @throws \Ellipse\Dispatcher\Exceptions\DispatcherCreationException
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        try {

            return new Dispatcher($handler, $middleware);

        }

        catch (TypeError $e) {

            throw new DispatcherCreationException($e);

        }
    }
}
