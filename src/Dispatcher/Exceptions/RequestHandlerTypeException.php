<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use TypeError;

use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerTypeException extends TypeError implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        $template = "Trying to use a value of type %s as request handler - object implementing %s expected";

        $type = is_object($value) ? get_class($value) : gettype($value);

        $msg = sprintf($template, $type, RequestHandlerInterface::class);

        parent::__construct($msg);
    }
}
