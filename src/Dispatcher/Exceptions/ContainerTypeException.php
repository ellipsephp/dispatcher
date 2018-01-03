<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use UnexpectedValueException;

use Psr\Container\ContainerInterface;

class ContainerTypeException extends UnexpectedValueException implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        $template = "A value of type %s was returned from the container factory - object implementing %s expected";

        $type = is_object($value) ? get_class($value) : gettype($value);

        $msg = sprintf($template, $type, ContainerInterface::class);

        parent::__construct($msg);
    }
}
