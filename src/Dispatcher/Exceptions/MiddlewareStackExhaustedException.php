<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use RuntimeException;

class MiddlewareStackExhaustedException extends RuntimeException implements DispatcherExceptionInterface
{
    public function __construct()
    {
        $msg = "The middleware stack is exhausted.";

        parent::__construct($msg);
    }
}
