<?php

namespace Pmall\Stack\Resolvers;

use Pmall\Contracts\Resolver\ResolverInterface;

use Pmall\Stack\MiddlewareStack;

class RecursiveResolver implements ResolverInterface
{
    /**
     * The underlying resolver to call when the thing to resolve is not an
     * iterable.
     *
     * @var \Pmall\Contracts\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * Set up a recursive resolver with the underlying resolver to decorate.
     *
     * @param \Pmall\Contracts\Resolver\ResolverInterface $resolver the decorated resolver.
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * If the thing to resolve is an iterable, resolve it by returning a
     * middleware stack with itself as resolver. Otherwise use the underlying
     * resolver.
     *
     * @param mixed $something the thing to resolve.
     * @return \Interop\Http\ServerMiddleware\MiddlewareInterface
     */
    public function resolve($something)
    {
        if (is_iterable($something)) {

            return new MiddlewareStack($this, $something);

        }

        return $this->resolver->resolve($something);
    }
}
