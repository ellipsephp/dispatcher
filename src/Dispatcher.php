<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

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
     * Return a new dispatcher with the given middleware and request handler.
     *
     * @param iterable                                      $elements
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     * @return \Ellipse\Dispatcher\Dispatcher
     */
    public static function getInstance(iterable $elements, RequestHandlerInterface $handler): Dispatcher
    {
        $stack = new MiddlewareStack($elements);

        return new Dispatcher($stack, $handler);
    }

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
