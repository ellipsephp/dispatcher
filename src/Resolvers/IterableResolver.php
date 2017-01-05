<?php

namespace Pmall\Stack\Resolvers;

use Pmall\Contracts\Resolver\ResolverInterface;
use Pmall\Contracts\Resolver\AbstractResolver;

use Pmall\Stack\MiddlewareStack;

class IterableResolver extends AbstractResolver
{
    /**
     * Return whether the element is an iterable.
     *
     * @param mixed $element the element which may be an iterable.
     * @return boolean
     */
    public function canResolve($element)
    {
        return is_iterable($element);
    }

    /**
     * Resolve the middleware from the iterable instance.
     *
     * @param mixed                                         $iterable the iterable to resolve.
     * @param \Pmall\Contracts\Resolver\ResolverInterface   $resolver the resolver to use.
     * @return \Interop\Http\ServerMiddleware\MiddlewareInterface
     */
    public function getMiddleware($iterable, ResolverInterface $resolver = null)
    {
        return new MiddlewareStack($resolver, $iterable);
    }
}
