<?php

namespace Pmall\Stack\Resolvers;

use Interop\Container\ContainerInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

use Pmall\Contracts\Resolver\AbstractResolver;

class ClassResolver extends AbstractResolver
{
    /**
     * The application container.
     *
     * @var \Interop\Container\ContainerInterface
    */
    private $container;

    /**
     * Sets up the class resolver with the application container.
     *
     * @param \Interop\Container\ContainerInterface $container the application container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Return whether the element is a class registered in the application
     * container.
     *
     * @param mixed $element the element which may be a class registered in the application container.
     * @return boolean
     */
    public function canResolve($element)
    {
        return is_string($element)
           and is_a($element, MiddlewareInterface::class, true)
           and $this->container->has($element);
    }

    /**
     * Resolve the middleware from the middleware class registered in the
     * application container.
     *
     * @param mixed $class the class to resolve.
     * @return \Interop\Http\ServerMiddleware\MiddlewareInterface
     */
    public function getMiddleware($class)
    {
        return $this->container->get($class);
    }
}
