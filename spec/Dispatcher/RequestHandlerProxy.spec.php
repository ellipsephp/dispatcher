<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerProxy;
use Ellipse\Dispatcher\Exceptions\RequestHandlerResolvingException;

describe('RequestHandlerProxy', function () {

    it('should implement RequestHandlerInterface', function () {

        $test = new RequestHandlerProxy('handler');

        expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        context('when the request handler implement RequestHandlerInterface', function () {

            it('should proxy the request handler', function () {

                $request = mock(ServerRequestInterface::class)->get();
                $response = mock(ResponseInterface::class)->get();

                $handler = mock(RequestHandlerInterface::class);

                $proxy = new RequestHandlerProxy($handler->get());

                $handler->handle->with($request)->returns($response);

                $test = $proxy->handle($request);

                expect($test)->toBe($response);

            });

        });

        context('when the request handler does not implement RequestHandlerInterface', function () {

            it('should throw a RequestHandlerResolvingException', function () {

                $request = mock(ServerRequestInterface::class)->get();

                $proxy = new RequestHandlerProxy('handler');

                $test = function () use ($request, $proxy) {

                    $proxy->handle($request);

                };

                $exception = new RequestHandlerResolvingException('handler');

                expect($test)->toThrow($exception);

            });

        });

    });

});
