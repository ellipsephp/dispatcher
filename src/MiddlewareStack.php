<?php declare(strict_types=1);

namespace Ellipse\Stack;

use Traversable;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Contracts\Stack\MiddlewareStackInterface;
use Ellipse\Contracts\Resolver\ResolverInterface;

class MiddlewareStack implements MiddlewareStackInterface
{
    /**
     * The resolver used to get middleware from the elements composing the
     * stack.
     *
     * @var \Ellipse\Contracts\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * The elements list composing the stack.
     *
     * @var array
     */
    private $elements = [];

    /**
     * Sets up a middleware stack with an optional resolver and an optional
     * elements list.
     *
     * @param iterable $elements
     */
    public function __construct(ResolverInterface $resolver = null, iterable $elements = [])
    {
        $this->resolver = $resolver ?: new DefaultResolver;

        $this->elements = $elements instanceof Traversable
            ? iterator_to_array($elements)
            : $elements;
    }

    /**
     * Shortcut for withMiddleware.
     *
     * @param mixed $element
     * @return \Ellipse\Contracts\Stack\MiddlewareStackInterface
     */
    public function with($element): MiddlewareStackInterface
    {
        return $this->withElement($element);
    }

    /**
     * Return a new middleware stack containing the given middleware.
     *
     * @param \Interop\Http\ServerMiddleware\MiddlewareInterface $middleware
     * @return \Ellipse\Contracts\Stack\MiddlewareStackInterface
    */
    public function withMiddleware(MiddlewareInterface $middleware): MiddlewareStackInterface
    {
        return $this->withElement($middleware);
    }

    /**
     * Return a new middleware stack containing the given element.
     *
     * @param mixed $element
     * @return \Ellipse\Contracts\Stack\MiddlewareStackInterface
    */
    public function withElement($element): MiddlewareStackInterface
    {
        $elements = array_merge($this->elements, [$element]);

        return new MiddlewareStack($this->resolver, $elements);
    }

    /**
     * Return a new stack using the given resolver.
     *
     * @param \Ellipse\Contracts\Resolver\ResolverInterface $resolver
     * @return \Ellipse\Contracts\Stack\MiddlewareStackInterface
     */
    public function withResolver(ResolverInterface $resolver): MiddlewareStackInterface
    {
        return new MiddlewareStack($resolver, $this->elements);
    }

    /**
     * Dispatch a request through the middleware stack and return the produced
     * response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $dispatcher = function () {

            $generator = function (callable $generator, $index = 0) {

                if (array_key_exists($index, $this->elements)) {

                    $element = $this->elements[$index];

                    $middleware = ! $element instanceof MiddlewareInterface
                        ? $this->resolver->resolve($element)
                        : $element;

                    return new Delegate($middleware, $generator($generator, $index + 1));

                }

                return new FinalDelegate;

            };

            return $generator($generator);

        };

        return $dispatcher()->process($request);
    }

    /**
     * Run the middleware stack as one middleware.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\DelegateInterface       $delegate
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this->withMiddleware(new FinalMiddleware($delegate))->dispatch($request);
    }
}
