<?php

namespace Pmall\Stack\Resolvers;

use Pmall\Contracts\Resolver\ResolverInterface;

class LazilyLoadedResolver extends ResolverInterface
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
     * {@inheritdoc}
     */
    public function resolve($element)
    {
        return $this->getResolver()->resolve($element);
    }
}
