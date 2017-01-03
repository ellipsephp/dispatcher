<?php

namespace Pmall\Stack;

use Traversable;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;

use Pmall\Contracts\Stack\MiddlewareStackInterface;
use Pmall\Contracts\Resolver\ResolverInterface;

use Pmall\Dispatcher\Dispatcher;

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
     * Return the middleware at the given index by resolving the corresponding
     * element.
     *
     * @param int $index the index of the element.
     * @return \Interop\Http\ServerMiddleware\MiddlewareInterface
     */
    public function get($index)
    {
        if (array_key_exists($index, $this->elements)) {

            // May throw a Pmall\Contracts\Resolver\ElementCantBeResolvedException.
            return $this->resolver->resolve($this->elements[$index]);

        }
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
        $stack = $this->withMiddleware(new FinalMiddleware($delegate));

        return (new Dispatcher($stack))->dispatch($request);
    }
}
