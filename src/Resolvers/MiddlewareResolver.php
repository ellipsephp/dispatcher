<?php

namespace Pmall\Stack\Resolvers;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

use Pmall\Contracts\Resolver\AbstractResolver;

class MiddlewareResolver extends AbstractResolver
{
    /**
     * Return whether the element is a middleware instance.
     *
     * @param mixed $element the element which may be a middleware instance.
     * @return boolean
     */
    public function canResolve($element)
    {
        return $element instanceof MiddlewareInterface;
    }

    /**
     * Return the middleware.
     *
     * @param mixed $middleware the middleware.
     * @return \Interop\Http\ServerMiddleware\MiddlewareInterface
     */
    public function getMiddleware($middleware)
    {
        return $middleware;
    }
}
