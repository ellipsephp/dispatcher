<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Traversable;
use RuntimeException;

use Interop\Http\Server\MiddlewareInterface;

class MiddlewareStack
{
    /**
     * The list of elements.
     *
     * @var array
     */
    private $elements;

    /**
     * The middleware resolver.
     *
     * @var callable
     */
    private $resolver;

    /**
     * Set up a middleware stack with the given elements and an optional
     * middleware resolver.
     *
     * @param iterable $elements
     * @param callable $resolver
     */
    public function __construct(iterable $elements, callable $resolver = null)
    {
        $this->elements = $elements instanceof Traversable
            ? iterator_to_array($elements)
            : $elements;

        $this->resolver = $resolver;
    }

    /**
     * Return whether the middleware stack is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->elements) == 0;
    }

    /**
     * Return a middleware proxy wrapped around the first element.
     *
     * @return \Interop\Http\Server\MiddlewareInterface;
     */
    public function head(): MiddlewareInterface
    {
        if ($this->isEmpty()) {

            throw new RuntimeException('The stack is exhausted');

        }

        $head = current($this->elements);

        return new MiddlewareProxy($head, $this->resolver);
    }

    /**
     * Return a new middleware stack composed of the remaining of the elements.
     *
     * @return \Ellipse\Dispatcher\MiddlewareStack;
     */
    public function tail(): MiddlewareStack
    {
        if ($this->isEmpty()) {

            throw new RuntimeException('The stack is exhausted');

        }

        $tail = array_slice($this->elements, 1);

        return new MiddlewareStack($tail, $this->resolver);
    }
}
