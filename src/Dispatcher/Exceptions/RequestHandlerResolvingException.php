<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use UnexpectedValueException;

class RequestHandlerResolvingException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        $msg = 'This value can\'t be resolved as a Psr-15 request handler: %s.';

        parent::__construct(sprintf($msg, print_r($value, true)));
    }
}
