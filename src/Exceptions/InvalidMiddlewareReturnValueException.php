<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use RuntimeException;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

use mindplay\readable;

use Ellipse\Contracts\Dispatcher\Exceptions\DispatcherExceptionInterface;

class InvalidMiddlewareReturnValueException extends RuntimeException implements DispatcherExceptionInterface
{
    public function __construct(MiddlewareInterface $middleware, $response)
    {
        parent::__construct(
            sprintf(
                'The value %s returned by the middleware %s is not a PSR 7 response.',
                readable::value($response),
                readable::value($middleware)
            )
        );
    }
}
