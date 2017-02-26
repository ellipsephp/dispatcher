<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Ellipse\Resolvers\RecursiveResolver;
use Ellipse\Resolvers\CallableResolver;

class DefaultResolver extends RecursiveResolver
{
    public function __construct()
    {
        parent::__construct(new CallableResolver);
    }
}
