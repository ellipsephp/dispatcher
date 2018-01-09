<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerDecorator;

describe('RequestHandlerDecorator', function () {

    beforeEach(function () {

        $this->middleware = mock(MiddlewareInterface::class);
        $this->delegate = mock(RequestHandlerInterface::class)->get();

        $this->handler = new RequestHandlerDecorator($this->middleware->get(), $this->delegate);

    });

    it('should implement RequestHandlerInterface', function () {

        expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        it('should proxy the middleware ->process() method with the given request and the request handler', function () {

            $request = mock(ServerRequestInterface::class)->get();
            $response = mock(ResponseInterface::class)->get();

            $this->middleware->process->with($request, $this->delegate)->returns($response);

            $test = $this->handler->handle($request);

            expect($test)->toBe($response);

        });

    });

});
