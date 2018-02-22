<?php declare(strict_types=1);

namespace Ellipse;

use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException;

class DispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * Return a new Dispatcher using the given request handler and iterable
     * middleware queue.
     *
     * @param mixed     $handler
     * @param iterable  $middleware
     * @return \Ellipse\Dispatcher
     * @throws \Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException
     */
    public function __invoke($handler, iterable $middleware = []): Dispatcher
    {
        if ($handler instanceof RequestHandlerInterface) {

            return new Dispatcher($handler, $middleware);

        }

        throw new RequestHandlerTypeException($handler);
    }
}
