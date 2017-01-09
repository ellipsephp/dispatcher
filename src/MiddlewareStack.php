<?php

namespace Pmall\Stack;

use Traversable;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;

use Pmall\Contracts\Stack\MiddlewareStackInterface;
use Pmall\Contracts\Resolver\ResolverInterface;

class MiddlewareStack implements MiddlewareStackInterface
{
    /**
     * The resolver used to get middleware from the elements composing the
     * stack.
     *
     * @var \Pmall\Contracts\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * The list of elements composing the stack.
     *
     * @var array
     */
    private $elements = [];

    /**
     * Sets up a middleware stack with an optional resolver and an optional
     * stack element.
     *
     * @param iterable $elements the default elements to push to the stack.
     */
    public function __construct(ResolverInterface $resolver = null, iterable $elements = [])
    {
        $this->resolver = $resolver ?: new DefaultResolver;

        if($elements instanceof Traversable) {

            $elements = iterator_to_array($elements);

        }

        array_map([$this, 'push'], $elements);
    }

    /**
     * Append something to the stack. Allow to ensure the thing can actually
     * be resolved when pushing a list of things.
     *
     * @param mixed $something the thing to push to the stack.
     * @return void
     */
    private function push($something)
    {
        $this->elements[] = $something;
    }

    /**
     * Shortcut for withMiddleware.
     *
     * @param mixed $something the thing to push.
     * @return \Pmall\Contracts\Stack\MiddlewareStackInterface
     */
    public function with($thing)
    {
        return $this->withMiddleware($thing);
    }

    /**
     * Return a new stack pushed with a given thing.
     *
     * @param mixed $something the thing to push.
     * @return \Pmall\Contracts\Stack\MiddlewareStackInterface
     */
     public function withMiddleware($thing)
     {
         $new = clone $this;

         $new->push($thing);

         return $new;
     }

    /**
     * Return a new stack with the given resolver.
     *
     * @param \Pmall\Contracts\Resolver\ResolverInterface $resolver the resolver of the new stack.
     * @return \Pmall\Contracts\Stack\MiddlewareStackInterface
     */
    public function withResolver(ResolverInterface $resolver)
    {
        $new = clone $this;

        $new->resolver = $resolver;

        return $new;
    }

    /**
     * Get a delegate containing a middleware resolved from the element at a
     * given index.
     *
     * @param int $index the index of the element to resolve.
     * @return \Interop\Http\ServerMiddleware\DelegateInterface
     */
    private function getDelegate($index)
    {
        if (array_key_exists($index, $this->elements)) {

            $element = $this->elements[$index];

            $middleware = $this->resolver->resolve($element);

            return new Delegate($middleware, $this->getDelegate($index + 1));

        }

        return new FinalDelegate;
    }

    /**
     * Dispatch a request through the middleware stack and return the produced
     * response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request the incoming request.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        return $this->getDelegate(0)->process($request);
    }

    /**
     * Implements psr 15 middleware convention.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request    the incoming request.
     * @param \Psr\Http\Message\DelegateInterface       $delegate   the next middleware to execute.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this($request, $delegate);
    }

    /**
    * Run the stack as one middleware.
    *
    * @param \Psr\Http\Message\ServerRequestInterface  $request    the incoming request.
    * @param \Psr\Http\Message\DelegateInterface       $delegate   the next middleware to execute.
    * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this->with(new FinalMiddleware($delegate))->dispatch($request);
    }
}
