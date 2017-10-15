<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use UnexpectedValueException;

use Interop\Http\Server\MiddlewareInterface;

use mindplay\readable;

class InvalidReturnValueException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($response)
    {
        parent::__construct(
            sprintf(
                'This value is not a Psr-7 response: %s',
                readable::value($response)
            )
        );
    }
}
