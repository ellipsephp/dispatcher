<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

describe('Dispatcher', function () {

    it('should implement RequestHandlerInterface', function () {

        $handler = mock(RequestHandlerInterface::class)->get();

        $test = new Dispatcher([], $handler);

        expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        context('when the given iterable list of middleware is not empty', function () {

            beforeEach(function () {

                $this->middleware = new class implements MiddlewareInterface
                {
                    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
                    {
                        $request = $request->withAttribute('key', 'value');

                        $response = $handler->handle($request);

                        return $response->withHeader('key', 'value');
                    }
                };

            });

            context('when all the middleware are implementations of MiddlewareInterface', function () {

                it('should dispatch the middleware in the order they are listed', function () {

                    $request1 = mock(ServerRequestInterface::class);
                    $request2 = mock(ServerRequestInterface::class);
                    $request3 = mock(ServerRequestInterface::class);
                    $response1 = mock(ResponseInterface::class);
                    $response2 = mock(ResponseInterface::class);
                    $response3 = mock(ResponseInterface::class);
                    $handler = mock(RequestHandlerInterface::class);

                    $test = function ($middleware) use ($request1, $response3, $handler) {

                        $dispatcher = new Dispatcher($middleware, $handler->get());

                        $test = $dispatcher->handle($request1->get());

                        expect($test)->toBe($response3->get());

                    };

                    $request1->withAttribute->with('key', 'value')->returns($request2);
                    $request2->withAttribute->with('key', 'value')->returns($request3);
                    $response1->withHeader->with('key', 'value')->returns($response2);
                    $response2->withHeader->with('key', 'value')->returns($response3);
                    $handler->handle->with($request3)->returns($response1);

                    $middleware = [$this->middleware, $this->middleware];

                    $test($middleware);
                    $test(new ArrayIterator($middleware));
                    $test(new class ($middleware) implements IteratorAggregate
                    {
                        public function __construct($middleware) { $this->middleware = $middleware; }
                        public function getIterator() { return new ArrayIterator($this->middleware); }
                    });

                });

            });

            context('when a middleware is not an implementation of MiddlewareInterface', function () {

                it('should throw a MiddlewareTypeException', function () {

                    $request1 = mock(ServerRequestInterface::class);
                    $request2 = mock(ServerRequestInterface::class);
                    $handler = mock(RequestHandlerInterface::class)->get();

                    $test = function ($middleware) use ($request1, $handler) {

                        $dispatcher = new Dispatcher($middleware, $handler);

                        $test = function () use ($request1, $dispatcher) {

                            $dispatcher->handle($request1->get());

                        };

                        $exception = new MiddlewareTypeException('middleware');

                        expect($test)->toThrow($exception);

                    };

                    $request1->withAttribute->with('key', 'value')->returns($request2);

                    $middleware = [$this->middleware, 'middleware'];

                    $test($middleware);
                    $test(new ArrayIterator($middleware));
                    $test(new class ($middleware) implements IteratorAggregate
                    {
                        public function __construct($middleware) { $this->middleware = $middleware; }
                        public function getIterator() { return new ArrayIterator($this->middleware); }
                    });
                });

            });

        });

        context('when the given iterable list of middleware is empty', function () {

            it('should proxy the handler ->handle() method', function () {

                $request = mock(ServerRequestInterface::class)->get();
                $response = mock(ResponseInterface::class)->get();
                $handler = mock(RequestHandlerInterface::class);

                $test = function ($middleware) use ($request, $response, $handler) {

                    $dispatcher = new Dispatcher($middleware, $handler->get());

                    $handler->handle->with($request)->returns($response);

                    $test = $dispatcher->handle($request);

                    expect($test)->toBe($response);

                };

                $test([]);
                $test(new ArrayIterator([]));
                $test(new class implements IteratorAggregate
                {
                    public function getIterator() { return new ArrayIterator([]); }
                });

            });

        });

    });

});
