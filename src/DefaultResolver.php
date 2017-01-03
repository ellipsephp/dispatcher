<?php

namespace Pmall\Stack;

use Pmall\Stack\Resolvers\CallableResolver;
use Pmall\Stack\Resolvers\MiddlewareResolver;
use Pmall\Stack\Resolvers\IterableResolver;
use Pmall\Stack\Resolvers\LazilyLoadedResolver;

class DefaultResolver extends ResolverAggregate
{
    public function __construct()
    {
        parent::__construct([
            new CallableResolver,
            new MiddlewareResolver,
            new IterableResolver(new LazilyLoadedResolver(function () {

                return new DefaultResolver;

            })),
        ]);
    }
}
