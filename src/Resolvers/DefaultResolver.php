<?php

namespace Pmall\Stack\Resolvers;

class DefaultResolver extends RecursiveResolver
{
    public function __construct()
    {
        parent::__construct(new ResolverAggregate([
            new CallableResolver,
            new MiddlewareResolver,
        ]));
    }
}
