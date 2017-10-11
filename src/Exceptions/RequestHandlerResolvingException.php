<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use UnexpectedValueException;

use mindplay\readable;

class RequestHandlerResolvingException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        parent::__construct(
            sprintf(
                'This value can\'t be resolved as a request handler: %s',
                readable::value($value)
            )
        );
    }
}
