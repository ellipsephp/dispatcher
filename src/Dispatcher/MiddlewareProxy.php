<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\MiddlewareResolvingException;

class MiddlewareProxy implements MiddlewareInterface
{
    /**
     * The element to use as a middleware.
     *
     * @var mixed
     */
    private $element;

    /**
     * The middleware resolver.
     *
     * @var callable
     */
    private $resolver;

    /**
     * Set up a middleware proxy with the given element to use as a middleware
     * and the given middleware resolver.
     *
     * @param mixed     $element
     * @param callable  $resolver
     */
    public function __construct($element, callable $resolver = null)
    {
        $this->element = $element;
        $this->resolver = $resolver;
    }

    /**
     * Proxy the middleware ->process() method. Resolve the element as a
     * middleware when needed.
     *
     * @param \Psr\Http\Message\ServerRequestInterface      $request
     * @param \Interop\Http\Server\RequestHandlerInterface  $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\MiddlewareResolvingException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->element instanceof MiddlewareInterface) {

            return $this->element->process($request, $handler);

        }

        if (! is_null($this->resolver)) {

            $resolved = ($this->resolver)($this->element);

            if ($resolved instanceof MiddlewareInterface) {

                return $resolved->process($request, $handler);

            }

        }

        throw new MiddlewareResolvingException($this->element);
    }
}
