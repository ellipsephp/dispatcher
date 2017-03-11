<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Dispatcher\Exceptions\NoResponseReturnedException;

class FinalDelegate implements DelegateInterface
{
    /**
     * Implements psr 15 middleware convention.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return void
     * @throws \Ellipse\Dispatcher\Exceptions\NoResponseReturnedException
     */
    public function process(ServerRequestInterface $request)
    {
        throw new NoResponseReturnedException;
    }
}
