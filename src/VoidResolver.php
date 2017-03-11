<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

use Ellipse\Resolvers\AbstractResolver;

class VoidResolver extends AbstractResolver
{
    /**
     * Just fail when this resolver is used.
     *
     * @param mixed $element
     * @return bool
     */
    public function canResolve($element): bool
    {
        return false;
    }

    /**
     * Never called.
     */
    public function getMiddleware($element): MiddlewareInterface
    {
        //
    }
}
