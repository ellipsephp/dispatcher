<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use RuntimeException;

use mindplay\readable;

class ElementIsNotAMiddlewareException extends RuntimeException implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        parent::__construct(
            sprintf(
                'The value %s is not a middleware.',
                readable::value($value)
            )
        );
    }
}
