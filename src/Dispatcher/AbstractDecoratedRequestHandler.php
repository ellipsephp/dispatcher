<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

abstract class AbstractDecoratedRequestHandler implements RequestHandlerInterface
{
    /**
     * The delegate.
     *
     * @var \Interop\Http\Server\RequestHandlerInterface
     */
    private $delegate;

    /**
     * Set up a decorated request handler with the given delegate. This class is
     * a base class for request handler decorators.
     *
     * @param \Interop\Http\Server\RequestHandlerInterface $delegate
     */
    public function __construct(RequestHandlerInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * Proxy the delegate.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->delegate->handle($request);
    }
}
