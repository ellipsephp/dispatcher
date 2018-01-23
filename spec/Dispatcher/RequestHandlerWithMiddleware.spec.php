<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerWithMiddleware;

describe('RequestHandlerWithMiddleware', function () {

    beforeEach(function () {

        $this->delegate = mock(RequestHandlerInterface::class)->get();
        $this->middleware = mock(MiddlewareInterface::class);

        $this->handler = new RequestHandlerWithMiddleware($this->delegate, $this->middleware->get());

    });

    it('should implement RequestHandlerInterface', function () {

        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        it('should proxy the middleware ->process() method with the given request and the delegate', function () {

            $request = mock(ServerRequestInterface::class)->get();
            $response = mock(ResponseInterface::class)->get();

            $this->middleware->process->with($request, $this->delegate)->returns($response);

            $test = $this->handler->handle($request);

            expect($test)->toBe($response);

        });

    });

});
