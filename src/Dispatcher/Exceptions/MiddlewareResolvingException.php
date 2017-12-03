<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use UnexpectedValueException;

class MiddlewareResolvingException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        $msg = "This value can't be resolved as a Psr-15 middleware: %s.";

        parent::__construct(sprintf($msg, print_r($value, true)));
    }
}
