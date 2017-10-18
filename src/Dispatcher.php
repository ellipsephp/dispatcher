<?php declare(strict_types=1);

namespace Ellipse;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\MiddlewareStack;
use Ellipse\Dispatcher\Exceptions\InvalidReturnValueException;

class Dispatcher implements RequestHandlerInterface
{
    /**
     * The middleware stack.
     *
     * @var \Ellipse\Dispatcher\MiddlewareStack
     */
    private $stack;

    /**
     * The request handler.
     *
     * @var \Psr\Http\Message\RequestHandlerInterface
     */
    private $handler;

    /**
     * Sets up a dispatcher with the given middleware stack and request handler.
     *
     * @param \Ellipse\Dispatcher\MiddlewareStack           $stack
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     */
    public function __construct(MiddlewareStack $stack, RequestHandlerInterface $handler)
    {
        $this->stack = $stack;
        $this->handler = $handler;
    }

    /**
     * Handle a request by processing it through all the middleware and the
     * request handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\InvalidReturnValueException
     */
    public function handle(ServerRequestInterface $request)
    {
        if (! $this->stack->isEmpty()) {

            $head = $this->stack->head();
            $tail = $this->stack->tail();

            $handler = new Dispatcher($tail, $this->handler);

            $response = $head->process($request, $handler);

        } else {

            $response = $this->handler->handle($request);

        }

        if (! $response instanceof ResponseInterface) {

            throw new InvalidReturnValueException($response);

        }

        return $response;
    }
}
