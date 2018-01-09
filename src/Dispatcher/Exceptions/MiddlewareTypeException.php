<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use TypeError;
use UnexpectedValueException;

use Interop\Http\Server\MiddlewareInterface;

class MiddlewareTypeException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($value, TypeError $previous)
    {
        $template = "Trying to use a value of type %s as middleware - object implementing %s expected";

        $type = is_object($value) ? get_class($value) : gettype($value);

        $msg = sprintf($template, $type, MiddlewareInterface::class);

        parent::__construct($msg, 0, $previous);
    }
}
