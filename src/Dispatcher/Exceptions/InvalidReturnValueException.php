<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use UnexpectedValueException;

class InvalidReturnValueException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($response)
    {
        $msg = 'This value is not a Psr-7 response: %s.';

        parent::__construct(sprintf($msg, print_r($response, true)));
    }
}
