<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use TypeError;
use RuntimeException;

class DispatcherCreationException extends RuntimeException implements DispatcherExceptionInterface
{
    public function __construct(TypeError $previous)
    {
        parent::__construct("Dispatcher creation failed.", 0, $previous);
    }
}
