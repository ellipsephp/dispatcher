<?php declare(strict_types=1);

namespace Ellipse\Stack;

use Ellipse\Resolvers\RecursiveResolver;
use Ellipse\Resolvers\ResolverAggregate;
use Ellipse\Resolvers\CallableResolver;
use Ellipse\Resolvers\MiddlewareResolver;

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
