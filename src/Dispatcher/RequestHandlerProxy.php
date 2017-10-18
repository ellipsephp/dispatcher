<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Exceptions\RequestHandlerResolvingException;

class RequestHandlerProxy implements RequestHandlerInterface
{
    /**
     * The element to use as a request handler.
     *
     * @var mixed
     */
    private $element;

    /**
     * The request handler resolver.
     *
     * @var callable
     */
    private $resolver;

    /**
     * Set up a request handler proxy with the given element to use as a request
     * handler and the given request handler resolver.
     *
     * @param mixed     $element
     * @param callable  $resolver
     */
    public function __construct($element, callable $resolver = null)
    {
        $this->element = $element;
        $this->resolver = $resolver;
    }

    /**
     * Get a request handler by resolving the element and proxy its ->handle()
     * method.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Dispatcher\Exceptions\RequestHandlerResolvingException
     */
    public function handle(ServerRequestInterface $request)
    {
        if ($this->element instanceof RequestHandlerInterface) {

            return $this->element->handle($request);

        }

        if (! is_null($this->resolver)) {

            $resolved = ($this->resolver)($this->element);

            if ($resolved instanceof RequestHandlerInterface) {

                return $resolved->handle($request);

            }

        }

        throw new RequestHandlerResolvingException($this->element);
    }
}
