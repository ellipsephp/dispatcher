<?php

namespace Pmall\Stack;

use Pmall\Resolvers\RecursiveResolver;
use Pmall\Resolvers\ResolverAggregate;
use Pmall\Resolvers\CallableResolver;
use Pmall\Resolvers\MiddlewareResolver;

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
