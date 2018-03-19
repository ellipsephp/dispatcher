<?php declare(strict_types=1);

namespace Ellipse\Dispatcher\Exceptions;

use TypeError;

use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Exceptions\TypeErrorMessage;

class RequestHandlerTypeException extends TypeError implements DispatcherExceptionInterface
{
    public function __construct($value)
    {
        $msg = new TypeErrorMessage('request handler', $value, RequestHandlerInterface::class);

        parent::__construct((string) $msg);
    }
}
