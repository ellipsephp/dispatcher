<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Dispatcher\Exceptions\ElementIsNotAMiddlewareException;

use Ellipse\Utils\IteratorFactory;

class Dispatcher implements DelegateInterface
{
    /**
     * The list of middleware.
     *
     * @var \Iterator
     */
    private $iterator;

    /**
     * The final delegate.
     *
     * @var \Psr\Http\Message\DelegateInterface
     */
    private $final;

    /**
     * Static method for creating a dispatcher.
     *
     * @param mixed                                 $middleware
     * @param \Psr\Http\Message\DelegateInterface   $delegate
     */
    public static function create($middleware = [], DelegateInterface $final = null)
    {
        return new Dispatcher($middleware, $final);
    }

    /**
     * Sets up a dispatcher with the given middleware list and an optional final
     * delegate.
     *
     * @param mixed                                 $middleware
     * @param \Psr\Http\Message\DelegateInterface   $delegate
     */
    public function __construct($middleware = [], DelegateInterface $final = null)
    {
        $this->iterator = IteratorFactory::create($middleware);
        $this->final = $final;
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
        // Reset the iterator so the dispatcher can be used multiple times.
        $this->iterator->rewind();

        // make a delegate out of the list of middleware and use it to process
        // the request.
        return $this->getNextDelegate()->process($request);
    }

    /**
     * Return the next delegate.
     *
     * @return \Psr\Http\Message\DelegateInterface
     */
    private function getNextDelegate(): DelegateInterface
    {
        if ($this->iterator->valid()) {

            $middleware = $this->iterator->current();

            $this->iterator->next();

            if (! $middleware instanceof MiddlewareInterface) {

                throw new ElementIsNotAMiddlewareException($middleware);

            }

            return new Delegate($middleware, $this->getNextDelegate());

        }

        return $this->final ?? new FinalDelegate;
    }
}
