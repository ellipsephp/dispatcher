<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use UnexpectedValueException;

use Psr\Http\Message\ResponseInterface;

class ResponseTypeException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        $template = "Trying to use a value of type %s as response - object implementing %s expected";

        $type = is_object($value) ? get_class($value) : gettype($value);

        $msg = sprintf($template, $type, ResponseInterface::class);

        parent::__construct($msg);
    }
}
