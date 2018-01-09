<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

describe('Dispatcher', function () {

    beforeEach(function () {

        $this->request1 = mock(ServerRequestInterface::class);
        $this->request2 = mock(ServerRequestInterface::class);
        $this->request3 = mock(ServerRequestInterface::class);
        $this->response1 = mock(ResponseInterface::class);
        $this->response2 = mock(ResponseInterface::class);
        $this->response3 = mock(ResponseInterface::class);
        $this->handler = mock(RequestHandlerInterface::class);

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

    it('should implement RequestHandlerInterface', function () {

        $test = new Dispatcher($this->handler->get(), []);

        expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->with()', function () {

        it('should return a new Dispatcher with the current one wrapped inside the given middleware', function () {

            $this->request1->withAttribute->with('key', 'value')->returns($this->request2);
            $this->request2->withAttribute->with('key', 'value')->returns($this->request3);
            $this->response1->withHeader->with('key', 'value')->returns($this->response2);
            $this->response2->withHeader->with('key', 'value')->returns($this->response3);
            $this->handler->handle->with($this->request3)->returns($this->response1);

            $dispatcher = new Dispatcher($this->handler->get());

            $test = $dispatcher
                ->with($this->middleware)
                ->with($this->middleware)
                ->handle($this->request1->get());

            expect($test)->toBe($this->response3->get());

        });

    });

    describe('->handle()', function () {

        context('when the iterable list of middleware is not empty', function () {

            context('when all the middleware are implementations of MiddlewareInterface', function () {

                it('should process the request with all the middleware before handling it with the decorated request handler', function () {

                    $test = function ($middleware) {

                        $dispatcher = new Dispatcher($this->handler->get(), $middleware);

                        $test = $dispatcher->handle($this->request1->get());

                        expect($test)->toBe($this->response3->get());

                    };

                    $this->request1->withAttribute->with('key', 'value')->returns($this->request2);
                    $this->request2->withAttribute->with('key', 'value')->returns($this->request3);
                    $this->response1->withHeader->with('key', 'value')->returns($this->response2);
                    $this->response2->withHeader->with('key', 'value')->returns($this->response3);
                    $this->handler->handle->with($this->request3)->returns($this->response1);

                    $middleware = [$this->middleware, $this->middleware];
                    $iterator = new ArrayIterator($middleware);
                    $aggregate = new class ($middleware) implements IteratorAggregate
                    {
                        public function __construct($middleware) { $this->middleware = $middleware; }
                        public function getIterator() { return new ArrayIterator($this->middleware); }
                    };

                    // the dispatcher should be usable multiple times.
                    $test($middleware);
                    $test($middleware);
                    $test($iterator);
                    $test($iterator);
                    $test($aggregate);
                    $test($aggregate);

                });

            });

            context('when a middleware is not an implementation of MiddlewareInterface', function () {

                it('should throw a MiddlewareTypeException', function () {

                    $test = function ($middleware) {

                        $dispatcher = new Dispatcher($this->handler->get(), $middleware);

                        $test = function () use ($dispatcher) {

                            $dispatcher->handle($this->request1->get());

                        };

                        $exception = new MiddlewareTypeException('middleware');

                        expect($test)->toThrow($exception);

                    };

                    $this->request1->withAttribute->with('key', 'value')->returns($this->request2);

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

        context('when the iterable list of middleware is empty', function () {

            it('should proxy the request handler', function () {

                $this->handler->handle->with($this->request1)->returns($this->response1);

                $dispatcher = new Dispatcher($this->handler->get());

                $test = $dispatcher->handle($this->request1->get());

                expect($test)->toBe($this->response1->get());

            });

        });

    });

});
