<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\ElementIsNotAMiddlewareException;

use Ellipse\Utils\IteratorFactory;

class Dispatcher implements RequestHandlerInterface
{
    /**
     * The list of middleware.
     *
     * @var \Iterator
     */
    private $iterator;

    /**
     * The final request handler.
     *
     * @var \Psr\Http\Message\RequestHandlerInterface
     */
    private $final;

    /**
     * Static method for creating a dispatcher with the given middleware list
     * and request handler.
     *
     * @param mixed                                         $middleware
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     * @return \Ellipse\Dispatcher\Dispatcher
     */
    public static function create($middleware, RequestHandlerInterface $handler): Dispatcher
    {
        return new Dispatcher($middleware, $handler);
    }

    /**
     * Sets up a dispatcher with the given middleware list and request handler.
     *
     * @param mixed                                         $middleware
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     */
    public function __construct($middleware, RequestHandlerInterface $handler)
    {
        $this->iterator = (new IteratorFactory)->getIterator($middleware);
        $this->handler = $handler;
    }

    /**
     * Handle a request by processing it with all the middleware and return the
     * produced response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Reset the iterator so the dispatcher can be used multiple times.
        $this->iterator->rewind();

        // make a handler out of the list of middleware and use it to handle
        // the request.
        return $this->getNextRequestHandler()->handle($request);
    }

    /**
     * Return the next request handler.
     *
     * @return \Psr\Http\Message\RequestHandlerInterface
     */
    private function getNextRequestHandler(): RequestHandlerInterface
    {
        if ($this->iterator->valid()) {

            $middleware = $this->iterator->current();

            $this->iterator->next();

            if (! $middleware instanceof MiddlewareInterface) {

                throw new ElementIsNotAMiddlewareException($middleware);

            }

            return new RequestHandler($middleware, $this->getNextRequestHandler());

        }

        return $this->handler;
    }
}
