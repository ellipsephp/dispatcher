<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

class RequestHandlerWithMiddlewareQueue extends RequestHandlerWithMiddlewareStack
{
    /**
     * Set up a request handler with middleware queue with the given delegate
     * and the middleware queue wrapping it.
     *
     * @param \Interop\Http\Server\RequestHandlerInterface  $delegate
     * @param array                                         $middleware
     * @throws \Ellipse\Dispatcher\Exceptions\MiddlewareTypeException
     */
    public function __construct(RequestHandlerInterface $delegate, array $middleware)
    {
        try {

            parent::__construct($delegate, array_reverse($middleware));

        }

        catch (MiddlewareTypeException $e)
        {
            throw $e;
        }
    }
}
