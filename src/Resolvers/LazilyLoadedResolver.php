<?php

namespace Pmall\Stack\Resolvers;

use Pmall\Contracts\Resolver\AbstractResolver;

class LazilyLoadedResolver extends AbstractResolver
{
    /**
     * The callaback used to load the resolver.
     *
     * @var callable
     */
    private $cb;

    /**
     * The lazily loaded resolver.
     *
     * @return \Pmall\Contracts\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * Set up the lazily loaded resolver with the callback used to load the
     * resolver.
     *
     * @param callable $cb the callaback used to load the resolver.
     */
    public function __construct(callable $cb)
    {
        $this->cb = $cb;
    }

    /**
     * Return the lazily loaded resolver by getting it from the cb the first
     * time.
     *
     * @return \Pmall\Contracts\Resolver\ResolverInterface
     */
    private function getResolver()
    {
        if (! $this->resolver) $this->resolver = ($this->cb)();

        return $this->resolver;
    }

    /**
     * Return whether the lazily loaded resolver can resolve this element.
     *
     * @param mixed $element the element which may be resolved.
     * @return boolean
     */
    public function canResolve($element)
    {
        return $this->getResolver()->canResolve($element);
    }

    /**
     * Resolve the middleware from the element.
     *
     * @param mixed $element the element to resolve.
     * @return \Interop\Http\ServerMiddleware\MiddlewareInterface
     */
    public function getMiddleware($element)
    {
        return $this->getResolver()->getMiddleware($element);
    }
}
