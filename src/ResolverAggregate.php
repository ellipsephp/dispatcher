<?php

namespace Pmall\Stack;

use Traversable;

use Pmall\Contracts\Resolver\ResolverInterface;
use Pmall\Contracts\Resolver\ElementCantBeResolvedException;

class ResolverAggregate implements ResolverInterface
{
    /**
     * The list of aggregated resolvers.
     *
     * @var array
     */
     private $resolvers = [];

    /**
     * Sets up the resolver with the list of underlying resolvers to run.
     *
     * @param iterable $resolvers the list of resolvers to run.
     */
    public function __construct(iterable $resolvers = [])
    {
        if($resolvers instanceof Traversable) {

            $resolvers = iterator_to_array($resolvers);

        }

        array_map([$this, 'aggregate'], $resolvers);
    }

    /**
     * Aggregate a resolver to the list. Allow to ensure the element is actually
     * a resolver interface when aggregating a list of resolvers.
     *
     * @param \Pmall\Contracts\Resolver\ResolverInterface $resolver the resolver to aggregate.
     * @return void
     */
    private function aggregate(ResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
    }

    /**
     * Return a new resolver aggregated with a given resolver.
     *
     * @param \Pmall\Contracts\Resolver\ResolverInterface $resolver the resolver to aggregate.
     * @return \Pmall\Stack\ResolverAggregate
     */
    public function withResolver(ResolverInterface $resolver)
    {
        $new = clone $this;

        $new->aggregate($resolver);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($element)
    {
        foreach ($this->resolvers as $resolver)
        {
            try {

                return $resolver->resolve($element);

            }

            catch (ElementCantBeResolvedException $e) {

                continue;

            }
        }

        throw new ElementCantBeResolvedException($element);
    }
}
