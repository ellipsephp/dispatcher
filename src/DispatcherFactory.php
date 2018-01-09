<?php declare(strict_types=1);

namespace Ellipse;

use TypeError;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException;

class DispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * Return a new Dispatcher using the given request handler and iterable
     * middleware queue. Ensure the given request handler is an implementation
     * of RequestHandlerInterface.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     * @throws \Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        try {

            return new Dispatcher($handler, $middleware);

        }

        catch (TypeError $e) {

            throw new RequestHandlerTypeException($handler, $e);

        }
    }
}
