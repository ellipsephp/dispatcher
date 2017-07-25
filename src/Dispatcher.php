<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Traversable;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

class Dispatcher implements DelegateInterface
{
    /**
     * The list of middleware.
     *
     * @var iterable
     */
    private $middleware = [];

    /**
     * The final delegate.
     *
     * @var \Psr\Http\Message\DelegateInterface
     */
    private $final;

    /**
     * Static method for creating a dispatcher.
     *
     * @param iterable                            $middleware
     * @param \Psr\Http\Message\DelegateInterface $delegate
     */
    public static function create(iterable $middleware = [], DelegateInterface $final = null)
    {
        return new Dispatcher($middleware, $final);
    }

    /**
     * Sets up a dispatcher with the given middleware list and an optional final
     * delegate.
     *
     * @param iterable                            $middleware
     * @param \Psr\Http\Message\DelegateInterface $delegate
     */
    public function __construct(iterable $middleware = [], DelegateInterface $final = null)
    {
        $this->middleware = $middleware;
        $this->final = $final ?: new FinalDelegate;
    }

    /**
     * Handle a request by processing it with all the middleware and return the
     * produced response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        // get an array when the middleware list is a Traversable instance
        $middleware = $this->middleware instanceof Traversable
            ? iterator_to_array($this->middleware)
            : $this->middleware;

        // make a delegate out of the list of middleware and use it to process
        // the request.
        return $this->getDelegate($middleware)->process($request);
    }

    /**
     * Return a delegate for the middleware at the given index.
     *
     * @param array $middleware
     * @param int   $index
     * @return \Psr\Http\Message\DelegateInterface
     */
    private function getDelegate(array $middleware, int $index = 0): DelegateInterface
    {
        if (array_key_exists($index, $middleware)) {

            return new Delegate($middleware[$index], $this->getDelegate($middleware, $index + 1));

        }

        return $this->final ?? new FinalDelegate;
    }
}
