<?php

namespace Pmall\Stack\Resolvers;

use Pmall\Contracts\Resolver\AbstractResolver;

use Pmall\Stack\Resolvers\Wrappers\CallableWrapper;

class CallableResolver extends AbstractResolver
{
    /**
     * Return whether the element is a callable object.
     *
     * @param mixed $element the element which may be a callable object.
     * @return boolean
     */
    public function canResolve($element)
    {
        return is_callable($element);
    }

    /**
     * Resolve the middleware from the callable object.
     *
     * @param mixed $callable the callable to resolve.
     * @return \Interop\Http\ServerMiddleware\MiddlewareInterface;
     */
    public function getMiddleware($callable)
    {
        return new CallableWrapper($callable);
    }
}
