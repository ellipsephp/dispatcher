<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

describe('Dispatcher', function () {

    beforeEach(function () {

        $this->delegate = mock(RequestHandlerInterface::class);

    });

    it('should implement RequestHandlerInterface', function () {

        $test = new Dispatcher($this->delegate->get(), []);

        expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('__construct()', function () {

        context('when a middleware in the given middleware queue does not implement MiddlewareInterface', function () {

            it('should throw a MiddlewareTypeException', function () {

                $test = function () {

                    new Dispatcher($this->delegate->get(), [
                        mock(MiddlewareInterface::class)->get(),
                        'middleware',
                        mock(MiddlewareInterface::class)->get(),
                    ]);

                };

                $exception = new MiddlewareTypeException('middleware');

                expect($test)->toThrow($exception);

            });

        });

    });

    describe('->with()', function () {

        it('should return a new Dispatcher with the current one wrapped inside the given middleware', function () {

            $middleware1 = mock(MiddlewareInterface::class)->get();
            $middleware2 = mock(MiddlewareInterface::class)->get();

            $dispatcher1 = new Dispatcher($this->delegate->get(), [$middleware1]);

            $test = $dispatcher1->with($middleware2);

            $dispatcher2 = new Dispatcher($dispatcher1, [$middleware2]);

            expect($test)->toEqual($dispatcher2);

        });

    });

    describe('->handle()', function () {

        context('when the middleware queue is empty', function () {

            it('should proxy the delegate', function () {

                $request = mock(ServerRequestInterface::class)->get();
                $response = mock(ResponseInterface::class)->get();

                $this->delegate->handle->with($request)->returns($response);

                $dispatcher = new Dispatcher($this->delegate->get());

                $test = $dispatcher->handle($request);

                expect($test)->toBe($response);

            });

        });

        context('when the middleware queue is not empty', function () {

            it('should process the request through the middleware queue before handling it with the delegate', function () {

                $middleware1 = new class implements MiddlewareInterface
                {
                    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
                    {
                        $request = $request->withAttribute('key1', 'value1');

                        $response = $handler->handle($request);

                        return $response->withHeader('key1', 'value1');
                    }
                };

                $middleware2 = new class implements MiddlewareInterface
                {
                    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
                    {
                        $request = $request->withAttribute('key2', 'value2');

                        $response = $handler->handle($request);

                        return $response->withHeader('key2', 'value2');
                    }
                };

                $request1 = mock(ServerRequestInterface::class);
                $request2 = mock(ServerRequestInterface::class);
                $request3 = mock(ServerRequestInterface::class);
                $response1 = mock(ResponseInterface::class);
                $response2 = mock(ResponseInterface::class);
                $response3 = mock(ResponseInterface::class);

                $request1->withAttribute->with('key1', 'value1')->returns($request2);
                $request2->withAttribute->with('key2', 'value2')->returns($request3);
                $response1->withHeader->with('key2', 'value2')->returns($response2);
                $response2->withHeader->with('key1', 'value1')->returns($response3);

                $this->delegate->handle->with($request3)->returns($response1);

                $dispatcher = new Dispatcher($this->delegate->get(), [
                    $middleware1,
                    $middleware2,
                ]);

                $test = $dispatcher->handle($request1->get());

                expect($test)->toBe($response3->get());

            });

        });

    });

});
