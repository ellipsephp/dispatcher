<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Traversable;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;
use Ellipse\Contracts\Resolver\ResolverInterface;

class Dispatcher implements DispatcherInterface
{
    /**
     * The resolver used to get middleware from the elements composing the
     * stack.
     *
     * @var \Ellipse\Contracts\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * The elements list composing the stack.
     *
     * @var array
     */
    private $elements = [];

    /**
     * Sets up a dispatcher with the given elements list and the given resolver.
     *
     * @param iterable                                      $elements
     * @param \Ellipse\Contracts\Resolver\ResolverInterface $resolver
     */
    public function __construct(iterable $elements = [], ResolverInterface $resolver = null)
    {
        $this->elements = $elements instanceof Traversable
            ? iterator_to_array($elements)
            : $elements;

        $this->resolver = $resolver ?: new VoidResolver;
    }

    /**
     * @{inheritdoc}
     */
    public function with($element): DispatcherInterface
    {
        $elements = array_merge($this->elements, [$element]);

        return new Dispatcher($elements, $this->resolver);
    }

    /**
     * @{inheritdoc}
     */
    public function withResolver(ResolverInterface $resolver): DispatcherInterface
    {
        return new Dispatcher($this->elements, $resolver);
    }

    /**
     * Dispatch a request through the middleware stack and return the produced
     * response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $dispatcher = function () {

            $generator = function (callable $generator, $index = 0) {

                if (array_key_exists($index, $this->elements)) {

                    $element = $this->elements[$index];

                    $middleware = ! $element instanceof MiddlewareInterface
                        ? $this->resolver->resolve($element)
                        : $element;

                    return new Delegate($middleware, $generator($generator, $index + 1));

                }

                return new FinalDelegate;

            };

            return $generator($generator);

        };

        return $dispatcher()->process($request);
    }

    /**
     * Run the dispatcher as one middleware.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\DelegateInterface       $delegate
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this->with(new FinalMiddleware($delegate))->dispatch($request);
    }
}
