<?php declare(strict_types=1);

namespace Ellipse;

use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException;

class DispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * Return a new Dispatcher using the given request handler and middleware
     * queue.
     *
     * @param mixed $handler
     * @param array $middleware
     * @return \Ellipse\Dispatcher
     * @throws \Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException
     */
    public function __invoke($handler, array $middleware = []): Dispatcher
    {
        if ($handler instanceof RequestHandlerInterface) {

            return new Dispatcher($handler, $middleware);

        }

        throw new RequestHandlerTypeException($handler);
    }
}
