<?php

namespace Pmall\Stack\Resolvers;

use Pmall\Contracts\Resolver\ResolverInterface;
use Pmall\Contracts\Resolver\AbstractResolver;

use Pmall\Stack\MiddlewareStack;

class IterableResolver extends AbstractResolver
{
    /**
     * The resolver to use in order to resolve the iterable elements.
     *
     * @var \Pmall\Contracts\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * Sets up the iterable resolver with the resolver to use.
     *
     * @param \Pmall\Contracts\Resolver\ResolverInterface $resolver the resolver to use.
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

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
     * @param mixed $iterable the iterable to resolve.
     * @return \Interop\Http\ServerMiddleware\MiddlewareInterface
     */
    public function getMiddleware($iterable)
    {
        return new MiddlewareStack($this->resolver, $iterable);
    }
}
