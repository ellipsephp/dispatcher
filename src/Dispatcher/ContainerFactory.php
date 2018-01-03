<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\Exceptions\ContainerTypeException;

class ContainerFactory
{
    /**
     * The delegate.
     *
     * @var callable
     */
    private $delegate;

    /**
     * Set up a container factory using the given deledate.
     *
     * @param callable $delegate
     */
    public function __construct(callable $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * Proxy the delegate and ensure a Psr-11 container is returned.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Container\ContainerInterface
     * @throws \Ellipse\Dispatcher\Exceptions\ContainerTypeException
     */
    public function __invoke(ServerRequestInterface $request): ContainerInterface
    {
        $container = ($this->delegate)($request);

        if ($container instanceof ContainerInterface) {

            return $container;

        }

        throw new ContainerTypeException($container);
    }
}
