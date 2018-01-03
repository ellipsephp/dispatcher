<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException;

class RequestHandlerProxy implements RequestHandlerInterface
{
    /**
     * The request handler.
     *
     * @var mixed
     */
    private $handler;

    /**
     * Set up a request handler proxy with the given request handler.
     *
     * @param mixed $handler
     */
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    /**
     * Proxy the request handler by ensuring it implements
     * RequestHandlerInterface.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->handler instanceof RequestHandlerInterface) {

            return $this->handler->handle($request);

        }

        throw new RequestHandlerTypeException($this->handler);
    }
}
