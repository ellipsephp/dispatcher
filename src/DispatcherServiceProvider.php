<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Interop\Container\ServiceProvider;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;
use Ellipse\Contracts\Resolver\ResolverInterface;

use Ellipse\Resolvers\CompositeResolver;

class DispatcherServiceProvider implements ServiceProvider
{
    public function getServices()
    {
        return [
            DispatcherInterface::class => function ($container) {

                $resolver = $container->get(ResolverInterface::class);

                return new Dispatcher([], $resolver);

            },

            ResolverInterface::class => function ($container, $previous = null) {

                $resolver = new VoidResolver;

                return ! is_null($previous)
                    ? new CompositeResolver($previous(), $resolver)
                    : $resolver;

            },
        ];
    }
}
