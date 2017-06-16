<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use RuntimeException;

use Ellipse\Contracts\Dispatcher\Exceptions\DispatcherExceptionInterface;

class NoResponseReturnedException extends RuntimeException implements DispatcherExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Middleware stack exhausted with no response.');
    }
}