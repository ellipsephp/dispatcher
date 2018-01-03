<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use UnexpectedValueException;

class RequestHandlerResolvingException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        $template = "A value of type %s can't be used as a Psr-15 request handler";

        $msg = sprintf($template, is_object($value) ? get_class($value) : gettype($value));

        parent::__construct($msg);
    }
}
